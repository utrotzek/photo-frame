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
<style>
    @import '/css/slideshow.css';
</style>

<script>
export default {
    data () {
        return {
            batchSize: 3,
            active:  0,
            pollCommandsInterval: null,
            slideshowInterval: null,
            pause: false,
            waitForExecution: false,
            enableTransition: true,
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
        },
        pollCommands: function() {
            if (!this.waitForExecution){
                axios.get('/api/commands?view=Slideshow')
                    .then(res => {
                        if (res.data.length > 0) {
                            this.waitForExecution = true;
                            this.executeCommands(res.data);
                            axios.delete('/api/commands/clearView/Slideshow');
                            this.waitForExecution = false;
                        }
                        this.commands = [];
                    });
                this.disableCommandInfos();
            }
        },
        executeCommands (commands) {
            this.enableTransition = false;
            for(let i=0; i < commands.length; i++) {
                const cmd = commands[i];
                switch (cmd.command) {
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
                        this.togglePause();
                        break;
                    case "play":
                        this.commandInfo.play = true;
                        this.togglePause();
                        this.setIntervals();
                        break;
                }
            }
            this.setIntervals();
        },
        triggerSlideshow: function(){
            this.enableTransition = true;
            if (!this.pause && !this.waitForExecution) {
                this.next();
            }
        },
        togglePause: function(){
            this.pause=!this.pause;
        },
        next: function() {
            axios.put('/api/queue/move', {direction: 'forward'})
                 .then(res => {
                    this.loadCurrentImage()
                 });
        },
        prev: function() {
            axios.put('/api/queue/move', {direction: 'backward'})
                .then(res => {
                    this.loadCurrentImage();
                });
        }
    }
};
</script>
