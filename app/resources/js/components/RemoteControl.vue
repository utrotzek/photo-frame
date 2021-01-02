<template>
    <div id="remote-control">
        <h1><i class="d-none d-sm-inline las la-hand-point-right"></i> Fernbedienung</h1>

        <div class="messages alert alert-success" :class="{'slide': successMessage}">
            {{ successMessage }}
        </div>

        <div class="messages alert alert-danger" :class="{'slide': errorMessage}">
            {{ errorMessage }}
        </div>

        <div class="row">
            <div class="col-3">
                <inline-svg name="owl"></inline-svg>
            </div>
            <div class="col-9">
                <p>
                    Steuern Sie hier die Bildershow auf ihrem Fotorahmen.
                </p>
            </div>
        </div>

        <h3>Fotos von 2019 - 2020</h3>
        <div id="player-information" class="row mb-3">
            <!-- /.info-box -->
            <div class="col-sm-6 col-12">
                <div class="row">
                    <div class="clm col-12">
                        <i class="info-icon las la-stream mr-1"></i>
                        <span class="info-text"><b>54</b> von <b>3400</b></span></div>
                </div>

                <div class="row">
                    <div class="clm col-12">
                        <i class="info-icon las la-image mr-1"></i>
                        <span class="info-text">IMG_2020.jpg</span></div>
                </div>

                <div class="row">
                    <div class="clm col-12">
                        <i class="info-icon las la-folder-open mr-1"></i>
                        <span class="info-text">Urlaub Ägypten</span></div>
                </div>

                <div class="row">
                    <div class="clm col-12">
                        <i class="info-icon las la-calendar mr-1"></i>
                        <span class="info-text">2019</span></div>
                </div>
            </div>
        </div>

        <div class="spinner-wrapper" :class="{ 'd-none': !loading }">
            <div class="spinner"></div>
        </div>

        <h3>Schnellauswahl</h3>
        <div class="accordion mb-2" id="quick-selection">
            <div id="years" class="card">
                <div class="card-header collapsed" id="headingOne" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                    <i class="las la-caret-square-right mr-1"></i> Nach Jahren
                </div>
                <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#quick-selection">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <select
                                    class="form-control"
                                    v-model="queue.yearSelection.from"
                                >
                                    <option disabled selected value="">Bitte wählen Sie ein Jahr</option>
                                    <option v-for="year in index.allYeas">
                                        {{ year }}
                                    </option>
                                </select>
                            </div>
                            <div class="col">
                                <select
                                    class="form-control"
                                    v-model="queue.yearSelection.to"
                                >
                                    <option disabled selected value="">Bitte wählen Sie ein Jahr</option>
                                    <option v-for="year in index.allYeas">
                                        {{ year }}
                                    </option>
                                </select>
                            </div>
                            <div class="col">
                                <button class="form-control btn btn-primary" @click="startQueueByYear">Los</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="playlist" class="card">
                <div class="card-header collapsed" id="headingTwo" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    <i class="las la-caret-square-right mr-1"></i> Playlist auswählen
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#quick-selection">
                    <div class="card-body">
                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                    </div>
                </div>
            </div>
            <div id="folder" class="card">
                <div class="card-header collapsed" id="headingThree" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    <i class="las la-caret-square-right mr-1"></i> Ordner auswählen
                </div>
                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#quick-selection">
                    <div class="card-body">
                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                    </div>
                </div>
            </div>
        </div>
        <div id="remote-control-bar" class="footer">
            <div class="row">
                <div class="col">
                    <button type="button" class="btn btn-secondary remote-button" @click="triggerAction('prev')"><i class="las la-step-backward"></i></button>
                </div>
                <div class="col">
                    <button
                        type="button"
                        class="btn btn-secondary remote-button"
                        @click="triggerAction('play')"
                        v-if="slideShowState === 'pause'"
                    >
                            <i class="las la-pause-circle"></i>
                    </button>

                    <button
                        type="button"
                        class="btn btn-secondary remote-button"
                        @click="triggerAction('pause')"
                        v-else
                    >
                            <i class="las la-play-circle"></i>
                    </button>


                </div>
                <div class="col">
                    <button type="button" class="btn btn-secondary remote-button" @click="triggerAction('next')"><i class="las la-step-forward"></i></button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import InlineSvg from "./InlineSvg";
import moment from "moment";
export default {
    components: {InlineSvg},
    data () {
        return {
            device: 'main',
            successMessage: '',
            errorMessage: '',
            loading: false,
            pendingAction: false,

            index: {
                allYeas: []
            },
            queue: {
                yearSelection: {
                    from: '',
                    to: ''
                }
            },
            slideShowState: null
        };
    },
    watch: {
        successMessage(val) {
            if (val){
                setTimeout(this.hideAlert, 5000)
            }
        },
        errorMessage(val) {
            if (val){
                setTimeout(this.hideAlert, 5000)
            }
        }
    },
    mounted() {
        this.loadYears();
        this.loadSlideshowState();
        setInterval(this.loadSlideshowState, 1000);
    },
    methods: {
        loadSlideshowState: function() {
            axios.get('/api/slideshow/' + this.device)
                 .then(res => {
                     this.slideShowState = res.data.action;
                 });
        },

        loadYears() {
            axios.get('/api/index/years')
                 .then(res => {
                     this.index.allYeas = res.data;

                     if (res.data.length > 0) {
                         const highestYear = res.data[res.data.length -1];
                         this.queue.yearSelection.from = highestYear;
                         this.queue.yearSelection.to = highestYear;
                     }
                 });
        },
        hideAlert() {
            this.successMessage = '';
            this.errorMessage = '';
        },
        triggerAction: function (action, queueTitle = '') {
            const actionParameter = {
                action: action,
                queue_title: queueTitle
            }
            axios.put('/api/slideshow/triggerNextAction/' + this.device, actionParameter);
        },
        startQueueByYear() {
            const queueData = {
                type: 'year',
                fromYear: this.queue.yearSelection.from,
                toYear: this.queue.yearSelection.to
            };
            this.loading = true;
            axios.post('/api/queue/create', queueData)
                .then(() => {
                    this.errorMessage = '';
                    this.successMessage = 'Eine Warteschlange von ' + this.queue.yearSelection.from + ' bis ' + this.queue.yearSelection.to + ' wurde erfolgreich erstellt.';
                })
                .catch(error => {
                    console.log(error)
                    this.errorMessage = 'Die Warteschlange konnte nicht erstellt werden. Bitte probieren Sie es erneut. Kontaktieren Sie ansonsten Ihren Administrator.'
                })
                .finally(res => {
                    this.loading = false;
                });
        }
    }
};
</script>

<style>
    .messages {
        position: absolute;
        left: 10px;
        top: 10px;
        visibility: hidden;
        opacity: 0;
        z-index: 99;
    }

    .messages.slide {
        visibility: visible;
        opacity: 1;
        transition: 1s;
    }

    #quick-selection .btn {
        padding: 0;
    }
    #quick-selection .las {
        font-size:1.3rem;
    }
    #quick-selection .card-header {
        padding-left: 0.2rem;
    }

    #quick-selection .card-header.collapsed .las:before {
        content: "\f152";
    }

    #quick-selection .card-header .las:before {
        content: "\f150";
    }

    .info-icon {
        font-size: 1.6rem;
    }

    .info-text {
        font-size: 1.0rem;
    }

    #player-information .row {
        margin-bottom: 1px;
    }

    #player-information .clm {
        background-color: #222;
    }

    #player-information ul {
        margin: 0;
        padding: 0;
    }
    #player-information ul li {
        list-style: none;
    }

    .icon {
        width: 45px;
    }

    body {
        padding-bottom: 50px;
    }

    .footer {
        position: fixed;
        color: white;
        text-align: center;
        background-color: #000;
        width: 100%;
        bottom: 0;
    }

    .remote-button {
        width:100%;
        height:50px;
    }
    #remote-control-bar .las {
        font-size: 45px;
    }
    .col {
        padding: 2px;
    }
</style>
