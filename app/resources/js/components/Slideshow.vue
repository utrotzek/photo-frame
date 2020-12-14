<template>
    <div id="slideshow">
        <div id="command-info-wrapper">
            <div id="command-info">
                <i class="icon las la-pause-circle"></i>
            </div>
        </div>


        <ul id="all_slides">
            <li
                v-for="(item) in images"
                :key="item.id"
                class="slide"
                :class="{active: item.active, 'no-transition': !enableTransition}"
                :style="{ backgroundImage: 'url(' + item.path + ')' }"
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
            active:  0,
            pollCommandsInterval: null,
            slideshowInterval: null,
            pause: false,
            waitForExecution: false,
            enableTransition: true,
            images: [
                {
                    id: 0,
                    path: "/images/1.jpg",
                    active: true
                },
                {
                    id: 1,
                    path: "/images/2.jpg",
                    active: false
                },
                {
                    id: 2,
                    path: "/images/3.jpg",
                    active: false
                }
            ]

        };
    },
    created() {
        this.setIntervals();
    },
    methods: {
        setIntervals () {
            clearInterval(this.slideshowInterval);
            clearInterval(this.pollCommandsInterval);
            this.slideshowInterval = setInterval(() => this.triggerSlideshow(), 5000);
            this.pollCommandsInterval = setInterval(() => this.pollCommands(), 1000);
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
            }
        },
        executeCommands (commands) {
            this.enableTransition = false;
            for(let i=0; i < commands.length; i++) {
                const cmd = commands[i];
                switch (cmd.command) {
                    case "next":
                        this.next();
                        break;
                    case "prev":
                        this.prev();
                        break;
                    case "pause":
                        this.togglePause();
                        break;
                    case "play":
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
            console.log(this.enableTransition);
            console.info("next-image");

            var next = this.active+1;

            if (next >= this.images.length){
                next = 0;
            }

            this.images[this.active].active = false;
            this.images[next].active = true;
            this.active = next;
        },
        prev: function() {
            var prev = this.active-1;

            if (prev < 0){
                prev = this.images.length-1;
            }

            this.images[this.active].active = false;
            this.images[prev].active = true;
            this.active = prev;
        }
    }
};
</script>
