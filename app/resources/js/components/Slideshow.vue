<template>
    <div id="slideshow">
        <ul id="all_slides">
            <li
                v-for="(item) in images"
                :key="item.id"
                class="slide"
                :class="{active: item.active}"
                :style="{ backgroundImage: 'url(' + item.path + ')' }"
            />
        </ul>

        <div class="buttons">
            <button class="controls" id="previous" @click="prev"><i class="far fa-arrow-alt-circle-left"></i></button>
            <button class="controls" id="pause" @click="togglePause"><i class="far fa-pause-circle"></i></button>
            <button class="controls" id="next" @click="next"><i class="far fa-arrow-alt-circle-right"></i></button>
        </div>
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
            pause: false,
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
        this.interval = setInterval(() => this.triggerSlideshow(), 10000);
    },
    methods: {
        triggerSlideshow: function(){
            if (!this.pause) {
                this.next();
            }
        },
        togglePause: function(){
            this.pause=!this.pause;
        },
        next: function() {
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
