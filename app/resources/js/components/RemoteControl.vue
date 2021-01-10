<template>
    <div id="remote-control">
        <h1><b-icon-controller class="d-none d-sm-inline las"></b-icon-controller> Fernbedienung</h1>

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

        <h3>{{ slideshow.queueTitle }}</h3>
        <div id="player-information" class="row mb-3">
            <!-- /.info-box -->
            <div class="col-sm-6 col-12">
                <div class="row">
                    <div class="clm col-12">
                        <b-icon-view-stacked class="ml-1 mr-1"></b-icon-view-stacked>
                        <span class="info-text"><b>{{ queue.statistics.current_position }}</b> von <b>{{ queue.statistics.total }}</b></span></div>
                </div>

                <div class="row">
                    <div class="clm col-12">
                        <b-icon-image class="ml-1 mr-1"></b-icon-image>
                        <span class="info-text">{{ queue.statistics.file_name }}</span></div>
                </div>

                <div class="row">
                    <div class="clm col-12">
                        <b-icon-folder2-open class="ml-1 mr-1"></b-icon-folder2-open>
                        <span class="info-text">{{ queue.statistics.album }}</span></div>
                </div>

                <div class="row">
                    <div class="clm col-12">
                        <b-icon-calendar-date class="ml-1 mr-1"></b-icon-calendar-date>
                        <span class="info-text">{{ queue.statistics.year }}</span></div>
                </div>
            </div>
        </div>

        <div class="spinner-wrapper" :class="{ 'd-none': !loading }">
            <div class="spinner"></div>
        </div>

        <h3>Andere Fotos abspielen</h3>
        <div class="accordion" role="tablist">
            <b-card no-body class="mb-1">
                <b-card-header header-tag="header" class="p-1" role="tab">
                    <b-button block v-b-toggle.accordion-1 variant="outline-success">Nach Jahren</b-button>
                </b-card-header>
                <b-collapse id="accordion-1" accordion="my-accordion" role="tabpanel">
                    <b-card-body>
                        <b-card-text><h3>Zeitraum auswählen</h3></b-card-text>
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
                    </b-card-body>
                </b-collapse>
            </b-card>
            <b-card no-body class="mb-1">
                <b-card-header header-tag="header" class="p-1" role="tab">
                    <b-button block v-b-toggle.accordion-2 variant="outline-success">Playliste auswählen</b-button>
                </b-card-header>
                <b-collapse id="accordion-2" accordion="my-accordion" role="tabpanel">
                    <b-card-body>
                        <b-card-text><h3>Playlist auswählen</h3></b-card-text>
                    </b-card-body>
                </b-collapse>
            </b-card>

            <b-card no-body class="mb-1">
                <b-card-header header-tag="header" class="p-1" role="tab">
                    <b-button block v-b-toggle.accordion-3 variant="outline-success">Ordner auswählen</b-button>
                </b-card-header>
                <b-collapse id="accordion-3" accordion="my-accordion" role="tabpanel">
                    <b-card-body>
                        <b-card-text><h3>Ordner auswählen</h3></b-card-text>
                    </b-card-body>
                </b-collapse>
            </b-card>
        </div>
        <div id="remote-control-bar" class="footer">
            <div class="row">
                <div class="col">
                    <button type="button" class="btn btn-secondary remote-button" @click="triggerAction('prev')">
                        <b-icon-skip-backward class="icon"></b-icon-skip-backward>
                    </button>
                </div>
                <div class="col">
                    <button
                        type="button"
                        class="btn btn-secondary remote-button"
                        @click="triggerAction('play')"
                        v-if="slideshow.state === 'pause'"
                    >
                        <b-icon-pause-circle class="icon"></b-icon-pause-circle>
                    </button>

                    <button
                        type="button"
                        class="btn btn-secondary remote-button"
                        @click="triggerAction('pause')"
                        v-else
                    >
                        <b-icon-play-circle class="icon"></b-icon-play-circle>
                    </button>


                </div>
                <div class="col">
                    <button type="button" class="btn btn-secondary remote-button" @click="triggerAction('next')">
                        <b-icon-skip-forward class="icon"></b-icon-skip-forward>
                    </button>
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
                },
                statistics: {
                    total: 0,
                    current_position: 0,
                    year: 0,
                    file_name: '',
                    album: '',
                }
            },
            slideshow: {
                state: null,
                queueTitle: ''
            }
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
        setInterval(this.loadSlideshowState, 2000);
    },
    methods: {
        loadSlideshowState: function() {
            axios.get('/api/slideshow/' + this.device)
                 .then(res => {
                     this.slideshow.state = res.data.action;
                     this.slideshow.queueTitle = res.data.queue_title;
                 });

            axios.get('/api/queue/statistics')
                .then(res => {
                    this.queue.statistics = {
                        total: res.data.total,
                        current_position: res.data.current_position,
                        year: res.data.year,
                        album: res.data.album,
                        file_name: res.data.file_name
                    }
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
            this.loadSlideshowState();
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
                    const queueTitle = 'Fotos von ' + this.queue.yearSelection.from + ' bis ' + this.queue.yearSelection.to;
                    this.triggerAction('start_queue', queueTitle);
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
    #remote-control-bar .icon {
        font-size: 2em;
    }
    .col {
        padding: 2px;
    }
</style>
