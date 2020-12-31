<template>
    <div id="remote-control">
        <div class="container">
            <h1>Fernbedienung</h1>
            <h2>Aktuelles Album: Urlaube</h2>
            <div class="row">
                <div class="col">
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <button type="button" class="btn btn-secondary" :disabled="prevLoading" @click="triggerCommand('prev')"><i class="las la-arrow-circle-left"></i> Zurück</button>
                        <button type="button" class="btn btn-secondary" :disabled="pauseLoading" @click="triggerCommand('pause')"><i class="las la-pause-circle"></i> Pause</button>
                        <button type="button" class="btn btn-secondary" :disabled="playLoading" @click="triggerCommand('play')"><i class="las la-pause-circle"></i> Play</button>
                        <button type="button" class="btn btn-secondary" :disabled="nextLoading" @click="triggerCommand('next')">Vor <i class="las la-arrow-alt-circle-right"></i> Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import InlineSvg from "./InlineSvg";
export default {
    components: {InlineSvg},
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
