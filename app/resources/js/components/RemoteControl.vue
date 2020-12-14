<template>
    <div id="remote-control">
        <div class="container">
            <h1>Fernbedienung</h1>
            <h2>Aktuelles Album: Urlaube</h2>
            <div class="row">
                <div class="col">
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <button type="button" class="btn btn-secondary"><i class="las la-arrow-circle-left"></i> Zurück</button>
                        <button type="button" class="btn btn-secondary"><i class="las la-pause-circle"></i> Pause</button>
                        <button type="button" class="btn btn-secondary">Vor <i class="las la-arrow-alt-circle-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

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
