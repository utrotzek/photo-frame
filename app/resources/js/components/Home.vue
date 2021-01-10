<template>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <h5 class="card-header"><b-icon-images class="mr-1"></b-icon-images> Bildershow starten</h5>
                    <div class="card-body d-flex flex-column">
                        <p>Die slideshow im Vollbild starten</p>
                        <router-link class="btn btn-primary mt-auto" to="/slideshow">Bildershow <b-icon-caret-right></b-icon-caret-right></router-link>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-4 mt-md-0">
                 <div class="card">
                     <h5 class="card-header"> <b-icon-controller class="mr-1"></b-icon-controller>Fernbedienung</h5>
                     <div class="card-body d-flex flex-column">
                         <p>Mithilfe der Fernbedienung haben Sie die Möglichkeit eine laufende Bildershow, die z.B. auf einem anderen Grät läuft, fernzusteuern.</p>
                         <router-link class="btn btn-primary mt-auto" to="/remote-control">Fernbedienung <b-icon-caret-right></b-icon-caret-right></router-link>
                     </div>
                 </div>
             </div>
            <div class="col-md-4 mt-4 mt-md-0">
                <div class="card">
                    <h5 class="card-header" ><b-icon-card-list class="mr-1"></b-icon-card-list> Bilder Index</h5>
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="row">
                            <div class="col">
                                <ul class="info-list">
                                    <li>Aktuell befinden sich <b>{{ index.totalFiles.toLocaleString() }}</b> Bilder im Index.</li>
                                    <li>Das neueste Bild wurde am <b>{{ index.newestFile }}</b> aufgenomen.</li>
                                    <li>Das älteste Bild ist vom <b>{{ index.oldestFile }}</b></li>
                                </ul>
                            </div>
                            <div class="col col-md-5 text-center">
                                <div class="progress-bar-wrapper mb-2">
                                    <vue-ellipse-progress
                                        :progress="indexState.percent"
                                        :color="progressBar.color"
                                        :loading="progressBar.loading"
                                        :dot="progressBar.dot"
                                        :size="110"
                                        :animation="progressBar.animation"
                                    >
                                        <span slot="legend-value">%</span>

                                        <p slot="legend-caption">
                                            {{ indexState.message }}<br />
                                        </p>
                                    </vue-ellipse-progress>
                                </div>
                            </div>
                        </div>
                        <button
                            class="btn btn-primary mt-auto"
                            @click="setTriggered()"
                            v-if="indexState.state !== 'working' && indexState.state !== 'triggered' && indexState.state !== 'starting'"
                        >
                            Indexierung starten <b-icon-caret-right></b-icon-caret-right></i>
                        </button>
                        <button
                            class="btn btn-danger"
                            v-else
                            @click="setAborted()"
                        >
                            Abbrechen <i class="lar la-times-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment';

export default {
    data () {
        return {
            colors: {
                startedColor: '#2a9fd6',
                waitingColor: '#77b300',
                workingColor: '#fd7e14',
                triggeredColor: '#e9ecef',
                failedColor: '#c00',
                abortColor: '#adafae'
            },
            animation: {
                quick: 'default 100',
                slow: 'default 2500'
            },
            progressBar: {
                color: '#77b300',
                loading: true,
                dot: '10',
                animation: 'default 100',
                slowDownOnNextTick: false
            },
            indexState: {
                state: 'loading',
                percent: 100,
                message: '',
                strokeColor: '#77b300',
            },
            index: {
                totalFiles: 0,
                newestFile: null,
                oldestFile: null
            },
            indexStatusInterval: null,
            indexInterval: null,
            currentInterval: 0
        };
    },
    components: {
    },
    mounted() {
        this.progressBar.animation = this.animation.quick;
        this.setDot();
        this.setReload(1000);

        this.pollStatistics();
        this.indexInterval = setInterval(() => this.pollStatistics(), 10000);
    },
    methods: {
        setReload(time) {
            if (this.currentInterval !== time) {
                this.currentInterval = time;
                clearInterval(this.indexStatusInterval);
                this.indexStatusInterval = setInterval(() => this.pollIndex(), time);
            }
        },
        setDot() {
            this.progressBar.dot = '10% '+ this.progressBar.color;
        },
        pollStatistics() {
            axios.get('/api/index/statistics')
                 .then(res => {
                    this.index.totalFiles = res.data.totalFiles;
                    this.index.newestFile = moment(res.data.newestFileDate).format('DD.MM.YYYY');
                    this.index.oldestFile = moment(res.data.oldestFileDate).format('DD.MM.YYYY');
                 });
        },
        pollIndex() {
            if (this.progressBar.slowDownOnNextTick){
                this.progressBar.animation = this.animation.slow;
                this.progressBar.slowDownOnNextTick = false;
            }
            axios.get('/api/index/state')
                 .then(res => {
                     switch (res.data.state) {
                         case 'waiting':
                            this.setModeWaiting();
                            break;
                         case 'working':
                             this.setModeWorking(res.data.percentage);
                             break;
                         case 'triggered':
                             this.setModeTriggered();
                             break;
                         case 'failed':
                             this.setModeFailed();
                             break;
                         case 'starting':
                             this.setModeStarting();
                             break;
                         case 'abort':
                             this.setModeAbort(res.data.percentage);
                             break;
                     }
                     this.setDot();
                 })
        },
        setTriggered() {
            axios.put('/api/index/update', {state: 'triggered'});
        },
        setAborted() {
            axios.put('/api/index/update', {state: 'abort'});
        },
        setModeWaiting() {
            this.setReload(2000);
            this.progressBar.color = this.colors.waitingColor;
            this.progressBar.loading = false;
            this.progressBar.animation = this.animation.quick;
            this.indexState = {
                state: 'waiting',
                percent: 100,
                message: 'Beendet'
            };
        },
        setModeWorking($percentage) {
            this.progressBar.color = this.colors.workingColor;
            this.progressBar.loading = false;
            this.progressBar.slowDownOnNextTick = true;
            this.setReload(1000);
            this.indexState = {
                state: 'working',
                percent: $percentage,
                message: 'Indexierung'
            };
        },
        setModeTriggered() {
            this.progressBar.color = this.colors.waitingColor;
            this.progressBar.loading = true;
            this.progressBar.animation = this.animation.quick;
            this.setReload(1000);
            this.indexState = {
                state: 'triggered',
                percent: 100,
                message: 'Wird gestartet'
            };
        },
        setModeStarting() {
            this.progressBar.color = this.colors.startedColor;
            this.progressBar.loading = true;
            this.setReload(1000);
            this.progressBar.animation = this.animation.quick;
            this.indexState = {
                state: 'starting',
                percent: 0,
                message: 'gestartet'
            };
            this.progressBar.loading = true;
        },
        setModeAbort(percentage) {
            this.progressBar.color = this.colors.abortColor;
            this.progressBar.loading = false;
            this.setReload(1000);
            this.progressBar.animation = this.animation.quick;
            this.indexState = {
                state: 'abort',
                percent: percentage,
                message: 'abgebrochen'
            };
            this.progressBar.loading = false;
        },
        setModeFailed() {
            this.progressBar.color = this.colors.failedColor;
            this.progressBar.loading = false;
            this.setReload(2000);
            this.progressBar.animation = this.animation.quick;
            this.indexState = {
                state: 'failed',
                percent: 100,
                message: 'Fehler'
            };
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

.progress-bar-circle circle {
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
