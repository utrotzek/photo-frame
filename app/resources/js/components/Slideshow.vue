<template>
    <div id="slideshow">
        <div id="command-info-wrapper">
            <div id="command-info">
                <i class="icon las la-pause-circle" :class="{active: commandInfo.pause}"></i>
                <i class="icon las la-play-circle" :class="{active: commandInfo.play}"></i>
                <i class="icon las la-step-forward" :class="{active: commandInfo.next}"></i>
                <i class="icon las la-step-backward" :class="{active: commandInfo.prev}"></i>
            </div>
        </div>

        <h1 class="info-message" :class="{'smooth': message}">
            {{ message }} <i class="icon las la-stream"></i>
        </h1>

        <div id="all_slides">
            <ul>
                <li
                    v-for="(item) in images"
                    :key="item.id"
                    class="slide background"
                    :class="{active: item.active, 'no-transition': !enableTransition}"
                    :style="{ backgroundImage: 'url(\'' + encodeURI(item.path) + '\')' }"
                >
                </li>
                <li
                    v-for="(item) in images"
                    :key="item.id"
                    class="slide foreground"
                    :class="{active: item.active, 'no-transition': !enableTransition}"
                    :style="{ backgroundImage: 'url(\'' + encodeURI(item.path) + '\')' }"
                />
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    data () {
        return {
            device: 'main',
            batchSize: 4,
            active:  0,
            pollCommandsInterval: null,
            slideshowInterval: null,
            pause: false,
            commandsProcessing: false,
            enableTransition: true,
            message: '',
            commandInfo: {
                pause: false,
                prev: false,
                next: false,
                play: false,
            },
            images: []

        };
    },
    created() {
        this.setIntervals();
    },
    mounted() {
        this.loadCurrentImage();
        this.loadPreviousBatch();
        this.loadNextBatch();
    },
    watch: {
        message(val) {
            if (val){
                setTimeout(() => {
                    this.message = '';
                }, 5000)
            }
        }
    },
    methods: {
        loadCurrentImage() {
            console.log("current function");
            axios.get('/api/queue/current')
                .then(res => {
                    let found = false, count = 0, foundIndex, currentImage;
                    this.images.forEach(image => {
                        if (image.id === res.data.id){
                            image.active = true;
                            currentImage = image;
                            found = true;
                            foundIndex=count;
                        }else{
                            image.active = false
                        }
                        count++;
                    });

                    if (found) {
                        if (foundIndex >= this.images.length-1){
                            //load next batch if last item is reached
                            this.loadNextBatch();
                        }else if (foundIndex === 0) {
                            //load previous batch if last item is reached
                            this.loadPreviousBatch();
                        }
                    } else {
                        const newImage = {
                            id: res.data.id,
                            path: res.data.file_path,
                            active: true
                        };
                        this.images.push(newImage)
                        this.loadNextBatch();
                        this.loadPreviousBatch();
                    }
                })
        },
        loadNextBatch() {
            axios.get('/api/queue/nextBatch?limit=' + this.batchSize)
                .then(res => {
                    res.data.forEach(imageItem => {
                        const found = this.images.some(el => el.id === imageItem.id);
                        //add if not exists already
                        if (!found){
                            this.images.push({
                                id: imageItem.id,
                                path: imageItem.file_path,
                                active: false
                            })
                        }
                    });
                });
        },
        loadPreviousBatch() {
            axios.get('/api/queue/previousBatch?limit=' + this.batchSize)
                .then(res => {
                    res.data.forEach(imageItem => {
                        const newItem = {
                            id: imageItem.id,
                            path: imageItem.file_path,
                            active: false
                        };
                        const found = this.images.some(el => el.id === imageItem.id);
                        if (!found){
                            this.images.unshift(newItem);
                        }
                    });
                });
        },
        garbageCollection () {
            let activeImageCount=0;
            for (let i=0; i < this.images.length; i++) {
                if (this.images[i].active){
                    activeImageCount=i+1;
                }
            }
            console.log(activeImageCount + "== " + this.batchSize * 2);
            if ((activeImageCount) >= this.batchSize * 2) {
                this.images.splice(0, this.batchSize*2-1);
            }
        },
        setIntervals () {
            clearInterval(this.slideshowInterval);
            clearInterval(this.pollCommandsInterval);
            this.slideshowInterval = setInterval(() => this.triggerSlideshow(), 30000);
            this.pollCommandsInterval = setInterval(() => this.pollCommands(), 1000);
        },
        disableCommandInfos () {
            this.commandInfo.next = false;
            this.commandInfo.pause = false;
            this.commandInfo.prev = false;
            this.commandInfo.play = false;
        },
        pollCommands: function() {
            if (!this.commandsProcessing){
                this.commandsProcessing = true;
                axios.get('/api/slideshow/' + this.device)
                    .then(res => {
                        switch (res.data.action) {
                            case 'play':
                                this.setPause(false)
                                break;
                            case 'pause':
                                this.setPause(true)
                                break;
                        }
                        if (res.data.next_action !== null) {
                            this.triggerAction(res.data.next_action, res.data.next_queue_title);
                            axios.put('/api/slideshow/nextActionDone/' + this.device);
                        }
                        this.commandsProcessing = false;
                    });
                this.disableCommandInfos();
            }
        },
        triggerAction (action, queueTitle) {
            this.enableTransition = false;
            switch (action) {
                case "next":
                    this.commandInfo.next = true;
                    this.next();
                    break;
                case "prev":
                    this.commandInfo.prev = true;
                    this.prev();
                    break;
                case "pause":
                    this.commandInfo.pause = true;
                    this.setPause(true);
                    break;
                case "play":
                    this.commandInfo.play = true;
                    this.setPause(false);
                    break;
                case "start_queue":
                    this.startQueue(queueTitle)

            }
            this.setIntervals();
        },
        triggerSlideshow: function(){
            this.enableTransition = true;
            if (!this.pause && !this.commandsProcessing) {
                this.next()
                    .then(res => {
                        this.garbageCollection();
                    })
            }
        },
        setPause(enabled) {
            this.pause = enabled;
        },
        next: function() {
            return new Promise((resolve, reject) => {
                axios.put('/api/queue/move', {direction: 'forward'})
                     .then(res => {
                        this.loadCurrentImage()
                        resolve()
                     });
            });
        },
        prev: function() {
            axios.put('/api/queue/move', {direction: 'backward'})
                .then(res => {
                    this.loadCurrentImage();
                });
        },
        startQueue(title) {
            axios.put('/api/queue/move', {direction: 'restart'})
                .then(res => {
                    this.images = [];
                    this.setPause(false);
                    this.message = 'Fotoshow ' + title + '\' wurde gestartet';

                    setTimeout(() => {
                        this.loadCurrentImage();
                        this.loadPreviousBatch();
                        this.loadNextBatch();
                    }, 5000)
                });
        }
    }
};
</script>

<style scoped>
.info-message {
    color: limegreen;
    mix-blend-mode: revert;
    font-size: 3rem;

    z-index: 99;
    bottom: 0;
    height: 100px;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    text-align: center;
    visibility: hidden;
    opacity: 0;
}

.info-message.smooth {
    visibility: visible;
    opacity: 1;
    transition: 1s;
}

#slideshow {
    cursor: none;
}

#all_slides {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.slide {
    -webkit-transition: opacity 10s;
    -moz-transition: opacity 10s;
    -o-transition: opacity 10s;
    transition: opacity 10s;
    overflow: hidden;
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    padding: 40px;
}

.slide.background {
    background-size: 110%;
    background-repeat: no-repeat;
    -webkit-filter: blur(50px) contrast(105%);
}

.slide.background.active {
    opacity: 1;
    z-index: 19;
    animation: zoom-in-and-out-background-image 30s infinite ease-in-out;
}

.slide.foreground {
    background-size: contain;
    background-position: 50% 50%;
    background-repeat: no-repeat;
    background-color: transparent;
}

.slide.foreground.active{
    opacity: 1;
    z-index: 20;
    animation: zoom-in-and-out-foreground-image 30s infinite ease-in-out;
}

.slide.no-transition {
    -webkit-transition: none;
    -moz-transition: none;
    -o-transition: none;
    transition: none;
}


#command-info .icon {
    display: none;
    z-index: 30;
    font-size: 8rem;
    bottom: 0;
    height: 100px;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
}

@keyframes zoom-in-and-out-background-image {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2,1.2);
    }
    100% {
        transform: scale(1);
    }
}

@keyframes zoom-in-and-out-foreground-image {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.04,1.04);
    }
    100% {
        transform: scale(1);
    }
}

@keyframes showCommandInfoIcon{
    0%{
        opacity: 90%;
        transform: rotateX(90deg);
    }
    50%{
        opacity: 80%;
        transform: rotateX(0deg);
    }
    100%{
        opacity: 0;
        display: none;
        transform: rotateX(90deg);
    }
}

#command-info .icon.active {
    display: block;
    animation-name: showCommandInfoIcon;
    animation-duration: 1000ms;
    animation-fill-mode: forwards;
}
</style>
