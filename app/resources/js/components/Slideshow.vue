<template>
    <div id="slideshow">
        <div id="command-info-wrapper">
            <div id="command-info">
                <i class="icon las la-stream" :class="{active: commandInfo.start_queue}"></i>
                <i class="icon las la-pause-circle" :class="{active: commandInfo.pause}"></i>
                <i class="icon las la-play-circle" :class="{active: commandInfo.play}"></i>
                <i class="icon las la-step-forward" :class="{active: commandInfo.next}"></i>
                <i class="icon las la-step-backward" :class="{active: commandInfo.prev}"></i>
            </div>
        </div>

        <h1 class="info-message" :class="{'slide': message}">
            {{ message }}
        </h1>

        <ul id="all_slides">
            <li
                v-for="(item) in images"
                :key="item.id"
                class="slide"
                :class="{active: item.active, 'no-transition': !enableTransition}"
                :style="{ backgroundImage: 'url(\'' + encodeURI(item.path) + '\')' }"
            />
        </ul>
    </div>
</template>

<script>
export default {
    data () {
        return {
            device: 'main',
            batchSize: 2,
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
                start_queue: false,
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
            this.slideshowInterval = setInterval(() => this.triggerSlideshow(), 10000);
            this.pollCommandsInterval = setInterval(() => this.pollCommands(), 1000);
        },
        disableCommandInfos () {
            this.commandInfo.next = false;
            this.commandInfo.pause = false;
            this.commandInfo.prev = false;
            this.commandInfo.play = false;
            this.commandInfo.start_queue = false;
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
                    this.commandInfo.start_queue = true;
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
                    }, 2000)
                });
        }
    }
};
</script>

<style scoped>
@import '/css/slideshow.css';

.info-message {
    position: absolute;
    left: 10px;
    top: 10px;
    visibility: hidden;
    opacity: 0;
    z-index: 99;
    width: 100%;
    text-align: center;
}

.info-message.slide {
    visibility: visible;
    opacity: 1;
    transition: 1s;
}
</style>
