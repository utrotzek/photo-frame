<template>
    <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <h5 class="card-header"><i class="lab la-buromobelexperte"></i> Bildershow starten</h5>
                        <div class="card-body d-flex flex-column">
                            <p>Die slideshow im Vollbild starten</p>
                            <router-link class="btn btn-primary mt-auto" to="/slideshow">Bildershow <i class="las la-caret-right"></i></router-link>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mt-4 mt-md-0">
                     <div class="card">
                         <h5 class="card-header"><i class="las la-hand-point-right"></i> Fernbedienung</h5>
                         <div class="card-body d-flex flex-column">
                             <p>Mithilfe der Fernbedienung haben Sie die Möglichkeit eine laufende Bildershow, die z.B. auf einem anderen Grät läuft, fernzusteuern.</p>
                             <router-link class="btn btn-primary mt-auto" to="/remote-control">Fernbedienung <i class="las la-caret-right"></i></router-link>
                         </div>
                     </div>
                 </div>
                <div class="col-md-4 mt-4 mt-md-0">
                    <div class="card">
                        <h5 class="card-header" ><i class="las la-clipboard-list"></i> Bilder Index</h5>
                        <div class="card-body d-flex flex-column">
                                <ul class="info-list">
                                    <li>Aktuell befinden sich <b>3.020</b> Bilder im Index.</li>
                                    <li>Das neueste Bild wurde am <b>20.12.2020</b> aufgenomen.</li>
                                    <li>Das älteste Bild am <b>01.01.1994.</b></li>
                                </ul>
                            <div class="progress-bar-wrapper mb-2">
                                <Progress
                                    :transitionDuration="5000"
                                    :radius="55"
                                    :strokeWidth="10"
                                    :strokeColor="strokeColor"
                                    value="86.12567"
                                >
                                    <template v-slot:footer>
                                        <b>Bilder werden indexiert</b>
                                    </template>
                                </Progress>
                            </div>

                            <button class="btn btn-primary mt-auto">Indexierung starten <i class="las la-caret-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Progress from '../../../node_modules/easy-circular-progress/src/index'

export default {
    data () {
        return {
            nextLoading: false,
            prevLoading: false,
            pauseLoading: false,
            playLoading: false,
            strokeColor: '#77b300'
            /* in ready strokeColor: '#77b300' */
            /* in waiting strokeColor: '#e9ecef' */
            /* in progress strokeColor: '#fd7e14' */
        };
    },
    components: {
        Progress
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
<style lang="scss" scoped>
@import '../../sass/_variables.scss';

.card {
    height:100%;
}

.btn {
    height:3em;
    line-height: 2em;
    font-size: 1.1em;
    font-weight: 300;
}

.vue-circular-progress {
    font-family: "Avenir", Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-align: center;
    color: $white;
    display: flex;
    justify-content: center;
    align-items: center;
}

ul.info-list{
    padding-inline-start: 20px;
}
</style>
