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
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="row">
                            <div class="col-8">
                                <ul class="info-list">
                                    <li>Aktuell befinden sich <b>3.020</b> Bilder im Index.</li>
                                    <li>Das neueste Bild wurde am <b>20.12.2020</b> aufgenomen.</li>
                                    <li>Das älteste Bild ist vom <b>01.01.1994.</b></li>
                                </ul>
                            </div>
                            <div class="col-4">
                                <div class="progress-bar-wrapper mb-2">
                                    <vue-ellipse-progress
                                        :progress="index.percent"
                                        :color="progressBar.color"
                                        :loading="progressBar.noData"
                                        :animation="progressBar.animation"
                                        :dot="progressBar.dot"
                                        :size="100"
                                    >
                                        <span slot="legend-value">%</span>
                                        <p slot="legend-caption">{{ index.message }}</p>
                                    </vue-ellipse-progress>
                                </div>
                            </div>
                        </div>
                        <button
                            class="btn btn-primary mt-auto"
                            :disabled="index.state === 'working' || index.state === 'loading'"
                        >
                            Indexierung starten <i class="las la-caret-right"></i>
                        </button>
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
            progress: 100000,
            colors: {
                waitingColor: '#77b300',
                workingColor: '#fd7e14',
                triggeredColor: '#e9ecef'
            },
            progressBar: {
                color: '#77b300',
                noData: true,
                dot: '',
                animation: 'bounce 1000 1000'
            },
            index: {
                state: 'loading',
                percent: 100,
                message: 'Warte',
                strokeColor: '#77b300',
            },
            indexStatusInterval: null,
            currentInterval: 0
        };
    },
    components: {
    },
    mounted() {
        this.setReload(1000);
        this.progressBar.dot = '10% '+ this.colors.workingColor;
    },
    methods: {
        setReload(time) {
            if (this.currentInterval !== time) {
                this.currentInterval = time;
                clearInterval(this.indexStatusInterval);
                this.indexStatusInterval = setInterval(() => this.pollIndex(), time);
            }
        },
        pollIndex() {
            axios.get('/api/index/state')
                 .then(res => {
                     switch (res.data.state) {
                         case 'waiting':
                            this.setModeWaiting();
                            break;
                         case 'working':
                             this.setModeWorking(res.data.percentage);
                             break;
                     }
                     this.progressBar.noData = false;
                 })
        },
        setModeWaiting() {
            this.setReload(2000);
            this.progressBar.color = this.colors.waitingColor;
            this.index = {
                state: 'waiting',
                percent: 100,
                message: 'Warte'
            };
        },
        setModeWorking($percentage) {
            this.progressBar.color = this.colors.workingColor;
            this.setReload(1000);
            this.index = {
                state: 'working',
                percent: $percentage,
                message: 'Indexierung'
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
