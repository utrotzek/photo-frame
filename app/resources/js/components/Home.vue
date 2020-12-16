<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="card">
                        <h5 class="card-header">Begin slideshow</h5>
                        <div class="card-body">
                            <h5 class="card-title">Start the nive slideshow</h5>
                            <router-link class="btn btn-primary" to="/slideshow">Slideshow</router-link>
                        </div>
                    </div>
                </div>
                 <div class="col">
                     <div class="card">
                         <h5 class="card-header">Remote control</h5>
                         <div class="card-body">
                             <h5 class="card-title">Control the slide show</h5>
                             <router-link class="btn btn-primary" to="/remote-control">Remote control</router-link>
                         </div>
                     </div>
                 </div>
                <div class="col">
                    <div class="card">
                        <h5 class="card-header">Remote control</h5>
                        <div class="card-body">
                            <h5 class="card-title">Index images</h5>
                            <button class="btn btn-primary">Index images</button>
                        </div>
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
            nextLoading: false,
            prevLoading: false,
            pauseLoading: false,
            playLoading: false
        };
    },
    methods: {
        triggerCommand: function (command) {
            const cmd = {
                view: 'Slideshow',
                command: command
            }
            axios.post('/api/commands', cmd);
        }
    }
};
</script>
