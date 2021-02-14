<template>
    <div id="slideshow" :style="cssProps">
        <div id="command-info-wrapper">
            <div id="command-info">
                <b-icon-arrow-counterclockwise class="icon" :class="{active: commandInfo.restart}"></b-icon-arrow-counterclockwise>
                <b-icon-pause-circle class="icon" :class="{active: commandInfo.pause}"></b-icon-pause-circle>
                <b-icon-play-circle class="icon" :class="{active: commandInfo.play}"></b-icon-play-circle>
                <b-icon-skip-forward class="icon" :class="{active: commandInfo.next}"></b-icon-skip-forward>
                <b-icon-skip-backward class="icon" :class="{active: commandInfo.prev}"></b-icon-skip-backward>
                <b-icon-clock class="icon" :class="{active: commandInfo.duration}"></b-icon-clock>
                <b-icon-heart-fill class="icon" :class="{active: commandInfo.add_favorite}"></b-icon-heart-fill>
                <b-icon-heart class="icon" :class="{active: commandInfo.remove_favorite}"></b-icon-heart>
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
//TODO: centralize this
const SLIDESHOW_ACTION_PLAY = 'play';
const SLIDESHOW_ACTION_STOP = 'stop';
const SLIDESHOW_ACTION_PREV = 'prev';
const SLIDESHOW_ACTION_NEXT = 'next';
const SLIDESHOW_ACTION_START_QUEUE = 'start_queue';
const SLIDESHOW_ACTION_RESTART = 'restart';
const SLIDESHOW_ACTION_RELOAD_CURRENT = 'reload_current';
const SLIDESHOW_ACTION_ADD_FAVORITE = 'add_favorite';
const SLIDESHOW_ACTION_REMOVE_FAVORITE = 'remove_favorite';
const SLIDESHOW_ACTION_UPDATE_SETTINGS_DURATION = 'settings_duration';

export default {
    data () {
        return {
            device: 'main',
            batchSize: 4,
            reloadTimeout: 5000,
            active:  0,
            pause: false,
            enableTransition: true,
            message: '',
            commandInfo: {
                restart: false,
                pause: false,
                prev: false,
                next: false,
                play: false,
                duration: false,
                add_favorite: false,
                remove_favorite: false,
            },
            settings: {
                //slide time in seconds
                imageSlideTime: 30
            },
            locks: {
                commandsProcessing: false,
                slideshowProcessing: false
            },
            interval: {
                updateSettingsInterval: null,
                pollCommandsInterval: null,
                slideshowInterval: null,
            },
            images: []
        };
    },
    computed: {
        cssProps() {
            return {
                '--slide-time': this.settings.imageSlideTime + 's'
            }
        },
        //TODO: remove redundancy (is also defined in remote control component
        durationOutput() {
            let duration = this.settings.imageSlideTime;

            if (duration < 60){
                return duration + " Sekunden";
            }else{
                const minutes = Math.floor(duration / 60);
                const minuteVerb = (minutes > 1) ? 'Minuten': 'Minute';
                const seconds = (duration - minutes * 60).toLocaleString('de-DE', {minimumIntegerDigits: 2, useGrouping: false});
                return minutes.toString() + ":" + seconds.toString() + " " + minuteVerb;
            }
        }
    },
    created() {
        this.refreshIntervals();
    },
    mounted() {
        this.loadCurrentImage();
        this.loadPreviousBatch();
        this.loadNextBatch();
        this.updateSettings();
    },
    watch: {
        message(val) {
            if (val){
                setTimeout(() => {
                    this.message = '';
                }, 5000)
            }
        },
        commandInfo: {
            deep: true,
            handler () {
                setTimeout(() => {
                        this.disableCommandInfos();
                }, 1000);
            }
        },
        settings: {
            deep: true,
            handler (val) {
                //refresh intervals when settings have changed. The duration could've been changed and the timeouts
                //need to be recalculated
                this.refreshIntervals();
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
        refreshIntervals () {
            clearInterval(this.interval.slideshowInterval);
            clearInterval(this.interval.pollCommandsInterval);
            clearInterval(this.interval.updateSettingsInterval);
            this.interval.slideshowInterval = setInterval(() => this.triggerSlideshow(), (this.settings.imageSlideTime * 1000));
            this.interval.pollCommandsInterval = setInterval(() => this.pollCommands(), 1000);
            this.interval.updateSettingsInterval = setInterval(() => this.updateSettings(), 10000);
        },
        disableCommandInfos () {
            this.commandInfo.next = false;
            this.commandInfo.pause = false;
            this.commandInfo.prev = false;
            this.commandInfo.play = false;
            this.commandInfo.restart = false;
            this.commandInfo.duration = false;
            this.commandInfo.add_favorite = false;
            this.commandInfo.remove_favorite = false;
        },
        pollCommands: function() {
            if (!this.locks.commandsProcessing){
                this.locks.commandsProcessing = true;
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
                            this.triggerAction(res.data.next_action, res.data.next_queue_title, res.data.next_duration);
                            axios.put('/api/slideshow/nextActionDone/' + this.device);
                        }
                    })
                    .finally(() => {
                        this.locks.commandsProcessing = false;
                    });
            }
        },
        updateSettings() {
            axios.get('/api/slideshow/' + this.device).then((res) => {
                if (this.settings.imageSlideTime !== res.data.duration) {
                    this.settings.imageSlideTime = res.data.duration;
                }
            });
        },
        //TODO: refactor this
        triggerAction (action, queueTitle, duration) {
            this.enableTransition = false;
            switch (action) {
                case "next":
                    this.commandInfo.next = true;
                    this.next();
                    break;
                case SLIDESHOW_ACTION_START_QUEUE:
                    this.startQueue(queueTitle);
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
                case SLIDESHOW_ACTION_ADD_FAVORITE:
                    this.commandInfo.add_favorite = true;
                    break;
                case SLIDESHOW_ACTION_REMOVE_FAVORITE:
                    this.commandInfo.remove_favorite = true;
                    break;
                case SLIDESHOW_ACTION_RELOAD_CURRENT:
                    this.loadCurrentImage();
                    break;
                case SLIDESHOW_ACTION_UPDATE_SETTINGS_DURATION:
                    this.updateDuration(duration);
                    break;
            }
        },
        triggerSlideshow: function(){
            this.enableTransition = true;
            if (!this.pause && !this.locks.commandsProcessing) {
                this.next()
                    .then(res => {
                        this.garbageCollection();
                    })
            }
        },
        updateDuration(duration) {
            const changed = this.settings.imageSlideTime !== duration;
            this.settings.imageSlideTime = duration;
            if (changed) {
                this.commandInfo.duration = true;
                this.message = 'Die Geschwindigkeit wurde auf ' + this.durationOutput + ' geändert';
            }
        },
        setPause(enabled) {
            this.pause = enabled;
        },
        triggerMove: function(direction) {
            if (!this.locks.slideshowProcessing) {
                this.locks.slideshowProcessing = true;
                return new Promise((resolve, reject) => {
                    axios.put('/api/queue/move', {direction: direction})
                        .then(res => {
                            if (direction !== 'restart'){
                                this.loadCurrentImage();
                            }
                            resolve()
                        })
                        .finally(() => {
                            this.locks.slideshowProcessing = false;
                        });
                });
            }
        },
        next: function() {
            return this.triggerMove('forward');
        },
        prev: function() {
            return this.triggerMove('backward');
        },
        restart: function() {
            return this.triggerMove('restart')
            .then(res => {
                this.reloadImages('Warteschlange wird neu gestartet.');
            });
        },
        startQueue(title) {
            return this.triggerMove('restart')
                .then(res => {
                    this.reloadImages('Fotoshow \'' + title + '\' wurde gestartet');
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
