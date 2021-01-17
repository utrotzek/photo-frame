<template>
    <div id="slideshow" :style="cssProps">
        <div id="command-info-wrapper">
            <div id="command-info">
                <b-icon-arrow-counterclockwise class="icon" :class="{active: commandInfo.restart}"></b-icon-arrow-counterclockwise>
                <b-icon-pause-circle class="icon" :class="{active: commandInfo.pause}"></b-icon-pause-circle>
                <b-icon-play-circle class="icon" :class="{active: commandInfo.play}"></b-icon-play-circle>
                <b-icon-skip-forward class="icon" :class="{active: commandInfo.next}"></b-icon-skip-forward>
                <b-icon-skip-backward class="icon" :class="{active: commandInfo.prev}"></b-icon-skip-backward>
            </div>
        </div>

        <h1 class="info-message" :class="{'smooth': message}">
            {{ message }}
        </h1>

        <div id="all_slides">
            <ul>
                <li
                    v-for="(item) in images"
                    class="slide background"
                    :class="{active: item.active, previous: item.previous, 'no-transition': !enableTransition}"
                    :style="{ backgroundImage: 'url(\'' + encodeURI(item.path) + '\')' }"
                />
                <li
                    v-for="(item) in images"
                    class="slide foreground"
                    :class="{active: item.active, previous: item.previous, 'no-transition': !enableTransition}"
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
            reloadTimeout: 5000,
            active:  0,
            pollCommandsInterval: null,
            slideshowInterval: null,
            pause: false,
            commandsProcessing: false,
            enableTransition: true,
            message: '',
            commandInfo: {
                restart: false,
                pause: false,
                prev: false,
                next: false,
                play: false,
            },
            settings: {
                //slide time in seconds
                imageSlideTime: 30
            },
            images: []

        };
    },
    computed: {
        cssProps() {
            return {
                '--slide-time': this.settings.imageSlideTime + 's'
            }
        }
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
            axios.get('/api/queue/current')
                .then(res => {
                    const currentId = res.data.id;
                    const newCurrentIndex = this.images.findIndex(item => {return item.id === currentId});

                    this.updateImageFlags();
                    if (newCurrentIndex > 0){
                        this.images[newCurrentIndex].active = true;
                    }else{
                        const newImage = {
                            id: res.data.id,
                            path: res.data.file_path,
                            active: true,
                            previous: false
                        };
                        this.images.push(newImage);
                    }
                    this.loadBatchesIfNecessary();
                })
        },
        updateImageFlags() {
            const activeIndex = this.images.findIndex(item => { return item.active === true;});
            const previousIndex = this.images.findIndex(item => { return item.previous === true;});
            if (activeIndex !== -1){

                this.images[activeIndex].previous = true;
                this.images[activeIndex].active = false;
            }
            if (previousIndex !== -1){
                this.images[previousIndex].previous = false;
            }
        },
        loadBatchesIfNecessary() {
            const activeIndex = this.images.findIndex(item => { return item.active === true});

            if (activeIndex <= 2) {
                this.loadPreviousBatch();
            }
            if (activeIndex >= this.images.length - 2) {
                this.loadNextBatch();
            }
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
                                active: false,
                                previous: false
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
                            active: false,
                            previous: false
                        };
                        const found = this.images.some(el => el.id === imageItem.id);
                        if (!found){
                            this.images.unshift(newItem);
                        }
                    });
                });
        },
        garbageCollection () {
            let activeImageIndex= this.images.findIndex(item => { return item.active === true});
            if ((activeImageIndex) >= this.batchSize * 3) {
                this.images.splice(0, this.batchSize-1);
            }
        },
        setIntervals () {
            clearInterval(this.slideshowInterval);
            clearInterval(this.pollCommandsInterval);
            this.slideshowInterval = setInterval(() => this.triggerSlideshow(), (this.settings.imageSlideTime * 1000));
            this.pollCommandsInterval = setInterval(() => this.pollCommands(), 1000);
        },
        disableCommandInfos () {
            this.commandInfo.next = false;
            this.commandInfo.pause = false;
            this.commandInfo.prev = false;
            this.commandInfo.play = false;
            this.commandInfo.restart = false;
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
                case "restart":
                    this.commandInfo.restart = true;
                    this.restart();
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
        restart: function() {
            axios.put('/api/queue/move', {direction: 'restart'})
                .then(res => {
                    this.reloadImages('Fotoshow wird neu gestartet.');
                });
        },
        startQueue(title) {
            axios.put('/api/queue/move', {direction: 'restart'})
                .then(res => {
                    this.reloadImages('Fotoshow ' + title + '\' wurde gestartet');
                });
        },
        reloadImages(message) {
            this.images = [];
            this.setPause(false);
            this.message = message;
            setTimeout(() => {
                this.loadCurrentImage();
                this.loadPreviousBatch();
                this.loadNextBatch();
            }, this.reloadTimeout);

        }
    }
};
</script>

<style scoped lang="scss">
@import "../../sass/variables";

.info-message {
    color: $teal;
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
    -webkit-transition: opacity 6s;
    -moz-transition: opacity 6s;
    -o-transition: opacity 6s;
    transition: opacity 6s;
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
    z-index: 15;
    background-size: 120%;
    background-position: center;
    background-repeat: repeat;
    background-color: transparent;
    -webkit-filter: blur(50px) contrast(105%);
}

.slide.background.active {
    opacity: 1;
    z-index: 16;
}

.slide.background.active {
    opacity: 1;
    z-index: 17;
}

.slide.foreground {
    z-index: 20;
    background-size: contain;
    background-position: 50% 50%;
    background-repeat: no-repeat;
    background-color: transparent;
}

.slide.foreground.active {
    opacity: 1;
    z-index: 22;
}

.slide.foreground.previous {
    z-index: 21;
}

.slide.no-transition {
    -webkit-transition: none;
    -moz-transition: none;
    -o-transition: none;
    transition: none;
}

#command-info .icon {
    color: $teal;
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


#command-info .icon.active {
    display: block;
    animation: show-info-icon 1s;
}

@keyframes show-info-icon {
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
</style>
