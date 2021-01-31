<template>
    <div id="remote-control" class="container-fluid">
        <b-row>
            <b-col>
                <h1><b-icon-controller class="d-none d-sm-inline las"></b-icon-controller> Fernbedienung</h1>
            </b-col>
            <b-col class="text-right">
                <button class="btn btn-secondary" @click="showSettings"><b-icon-gear></b-icon-gear></button>
            </b-col>
        </b-row>
        <div class="messages alert alert-success" :class="{'slide': successMessage}">
            {{ successMessage }}
        </div>

        <div class="messages alert alert-danger" :class="{'slide': errorMessage}">
            {{ errorMessage }}
        </div>

        <h3>Aktuell angezeigt</h3>
        <div id="player-information">
            <b-row>
                <b-col cols="12" class="clm">
                    Playlist: {{ slideshow.queueTitle }}
                </b-col>
            </b-row>
            <b-row>
                <b-col cols="12" class="clm">
                    <b-icon-view-stacked class="ml-1 mr-1"></b-icon-view-stacked>
                    <span class="info-text"><b>{{ queue.statistics.current_position }}</b> von <b>{{ queue.statistics.total }}</b></span>
                </b-col>
            </b-row>
            <b-row>
                <b-col cols="12" class="clm">
                    <b-icon-image class="ml-1 mr-1"></b-icon-image>
                    <span class="info-text">{{ queue.statistics.file_name }}</span>
                </b-col>
            </b-row>
            <b-row>
                <b-col cols="12" class="clm">
                    <b-icon-folder2-open class="ml-1 mr-1"></b-icon-folder2-open>
                    <span class="info-text">{{ queue.statistics.album }}</span>
                </b-col>
            </b-row>
            <b-row>
                <b-col cols="12" class="clm">
                    <b-icon-calendar-date class="ml-1 mr-1"></b-icon-calendar-date>
                    <span class="info-text">{{ queue.statistics.year }}</span>
                </b-col>
            </b-row>
            <b-row class="mt-1">
                <b-col cols="12">
                    <b-button-group class="d-flex">
                        <b-button class="mr-1" @click="$refs['delete-picture-modal'].show()"><b-icon-trash></b-icon-trash> Foto löschen</b-button>
                        <b-button @click="toggleFavorite">
                            <b-icon-heart v-if="!queue.statistics.favorite"></b-icon-heart>
                            <b-icon-heart-fill v-else></b-icon-heart-fill>
                            Als Favorit markieren
                        </b-button>
                    </b-button-group>
                </b-col>
            </b-row>
        </div>

        <h3>Andere Fotos abspielen</h3>
        <div id="image-control" class="accordion" role="tablist">
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
                                <b-button block variant="primary" @click="yearSelected" >Los</b-button>
                            </div>
                        </div>
                    </b-card-body>
                </b-collapse>
            </b-card>
            <b-card no-body class="mb-1">
                <b-card-header header-tag="header" class="p-1" role="tab">
                    <b-button block v-b-toggle.accordion-3 variant="outline-success">Album auswählen</b-button>
                </b-card-header>
                <b-collapse id="accordion-3" class="album-selector" accordion="my-accordion" role="tabpanel">
                    <b-card-body>
                        <b-card-text><h3>Album auswählen</h3></b-card-text>
                        <b-card-body>
                            <AlbumSelector :key="albumSelectorKey" @selected="albumsSelected"></AlbumSelector>
                        </b-card-body>
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
        </div>
        <div id="remote-control-bar" class="footer row">
            <div class="col">
                <button type="button" class="btn btn-secondary remote-button" @click="triggerActionSimple('prev')">
                    <b-icon-skip-backward class="icon"></b-icon-skip-backward>
                </button>
            </div>
            <div class="col">
                <button type="button" class="btn btn-secondary remote-button" @click="triggerActionSimple('restart')">
                    <b-icon-arrow-counterclockwise class="icon"></b-icon-arrow-counterclockwise>
                </button>
            </div>
            <div class="col">
                <button
                    type="button"
                    class="btn btn-secondary remote-button"
                    @click="triggerActionSimple('play')"
                    v-if="slideshow.state === 'pause'"
                >
                    <b-icon-pause-circle class="icon"></b-icon-pause-circle>
                </button>

                <button
                    type="button"
                    class="btn btn-secondary remote-button"
                    @click="triggerActionSimple('pause')"
                    v-else
                >
                    <b-icon-play-circle class="icon"></b-icon-play-circle>
                </button>


            </div>
            <div class="col">
                <button type="button" class="btn btn-secondary remote-button" @click="triggerActionSimple('next')">
                    <b-icon-skip-forward class="icon"></b-icon-skip-forward>
                </button>
            </div>
        </div>

        <!-- Modals -->
        <b-modal id="delete-picture-modal" ref="delete-picture-modal" title="Foto wirklich löschen?" hide-footer centered>
            <p class="my-4">Wollen Sie das Foto wirklich löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.</p>
            <b-button-group>
                <b-button @click="$refs['delete-picture-modal'].hide()">Abbrechen</b-button>
                <b-button @click="deletePicture" variant="danger">Löschen</b-button>
            </b-button-group>
        </b-modal>

        <b-modal id="settings-modal" ref="settings-modal" v-model="settingsVisible" centered title="Einstellungen" @ok="saveSettings">
            <b-form-group label="Geschwindigkeit">
                <b-input-group>
                    <b-form-input
                        id="slideshow-duration"
                        v-model="slideshow.duration"
                        type="range"
                        number
                        min="10"
                        max="600"
                        step="10"
                    ></b-form-input>
                    <b-input-group-append is-text class="text-monospace">
                        {{ durationOutput }}
                    </b-input-group-append>
                </b-input-group>
            </b-form-group>
        </b-modal>
        <b-modal id="queue-order-modal" ref="queue-order-modal" centered title="Neue Playlist starten" hide-footer>

            <p>Eine neue playlist wird gestaret. Bitte wählen Sie aus, in welcher Reihenfolge die Bilder abgespielt werden sollen.</p>
            <div class="row">
                <div class="col" v-if="queueMode === 'album'">
                    <label for="album-title">Titel</label>
                    <b-input
                        id="album-title"
                        v-model="queue.albumSelection.title"
                        placeholder="Warteschlangen Titel"
                        block
                    ></b-input>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <b-button variant="success" block @click="startQueue(true)">Zufällig</b-button>
                </div>
                <div class="col">
                    <b-button variant="warning" block @click="startQueue(false)">Chronologisch</b-button>
                </div>
                <div class="col">
                    <b-button variant="danger " block @click="abortQueue">Abbrechen</b-button>
                </div>
            </div>
        </b-modal>

        <!-- loading spinner-->
        <div class="spinner-wrapper" :class="{ 'd-none': !loading }">
            <div class="spinner"></div>
        </div>
    </div>
</template>

<script>
import InlineSvg from "./InlineSvg";
import moment from "moment";
import AlbumSelector from "./tools/AlbumSelector";

const SLIDESHOW_ACTION_PLAY = 'play';
const SLIDESHOW_ACTION_STOP = 'stop';
const SLIDESHOW_ACTION_PREV = 'prev';
const SLIDESHOW_ACTION_NEXT = 'next';
const SLIDESHOW_ACTION_RELOAD_CURRENT = 'reload_current';
const SLIDESHOW_ACTION_ADD_FAVORITE = 'add_favorite';
const SLIDESHOW_ACTION_REMOVE_FAVORITE = 'remove_favorite';
const SLIDESHOW_ACTION_START_QUEUE = 'start_queue';
const SLIDESHOW_ACTION_RESTART = 'restart';
const SLIDESHOW_ACTION_UPDATE_SETTINGS_DURATION = 'settings_duration';

const QUEUE_MODE_YEAR = 'year';
const QUEUE_MODE_ALBUM = 'album';

export default {
    components: {InlineSvg, AlbumSelector},
    data () {
        return {
            device: 'main',
            successMessage: '',
            errorMessage: '',
            loading: false,
            pendingAction: false,
            queueMode: null,
            settingsVisible: false,
            albumSelectorKey: 0,
            index: {
                allYeas: []
            },
            queue: {
                yearSelection: {
                    from: '',
                    to: ''
                },
                albumSelection: {
                    albumList: [],
                    title: ''
                },
                statistics: {
                    total: 0,
                    current_position: 0,
                    year: 0,
                    file_name: '',
                    album: '',
                    favorite: false,
                    index_id: 0
                }
            },
            slideshow: {
                state: null,
                queueTitle: '',
                duration: 30
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
    computed: {
        durationOutput() {
            let duration = this.slideshow.duration;

            if (duration < 60){
                return duration + " Sekunden";
            }else{
                const minutes = Math.floor(duration / 60);
                const minuteVerb = (minutes > 1) ? 'Minuten': 'Minute';
                const seconds = (duration - minutes * 60).toLocaleString('de-DE', {minimumIntegerDigits: 2, useGrouping: false});
                return minutes.toString() + ":" + seconds.toString() + " " + minuteVerb;
            }
        }
    },
    methods: {
        loadSlideshowState: function() {
            axios.get('/api/slideshow/' + this.device)
                 .then(res => {

                     if (!this.settingsVisible) {
                        this.slideshow.duration = res.data.duration;
                     }

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
                        file_name: res.data.file_name,
                        favorite: res.data.favorite,
                        index_id: res.data.index_id
                    }
                });
        },
        yearSelected() {
            this.queueMode = QUEUE_MODE_YEAR;
            this.showQueueModal();
        },
        albumsSelected(albums) {
            this.queueMode = QUEUE_MODE_ALBUM;
            this.queue.albumSelection.albumList =  albums;

            if (this.queue.albumSelection.albumList.length  === 1){
                const path = this.queue.albumSelection.albumList[0];
                this.queue.albumSelection.title = String(path).substring(path.lastIndexOf('/') + 1);
            } else {
                this.queue.albumSelection.title = '';
            }

            this.showQueueModal();
        },
        loadYears() {
            axios.get('/api/index/years')
                 .then(res => {
                     this.index.allYeas = res.data;
                     this.selectLatestYears();
                 });
        },
        selectLatestYears() {
            if (this.index.allYeas > 0) {
                const highestYear = this.index.allYeas[this.index.allYeas -1];
                this.queue.yearSelection.from = highestYear;
                this.queue.yearSelection.to = highestYear;
            }
        },
        hideAlert() {
            this.successMessage = '';
            this.errorMessage = '';
        },
        triggerActionSettingDuration(duration) {
            const actionParameter = {
                action: SLIDESHOW_ACTION_UPDATE_SETTINGS_DURATION,
                duration: duration
            }
            return this.sendTriggerAction(actionParameter);
        },
        triggerActionQueue(queueTitle) {
            const actionParameter = {
                action: SLIDESHOW_ACTION_START_QUEUE,
                queue_title: queueTitle
            }
            return this.sendTriggerAction(actionParameter);
        },
        triggerActionSimple: function (action) {
            const actionParameter = {
                action: action
            }
            this.sendTriggerAction(actionParameter);
        },
        sendTriggerAction(actionParameter){
            return new Promise((resolve, reject) => {
                this.loading = true;
                axios.put('/api/slideshow/triggerNextAction/' + this.device, actionParameter)
                    .then(() => {
                        this.loading = false;
                    })
                    .catch(error => {
                        this.loading = false;
                        this.errorMessage = error;
                        reject(error)
                    })
                    .finally(() => {
                        this.loadSlideshowState();
                        resolve();
                    })
                ;
            });
        },
        startQueue(random) {
            let queueData = null, message = '', queueTitle = '', defaultTitle = '';
            switch (this.queueMode){
                case QUEUE_MODE_YEAR:
                    queueTitle = 'Fotos von ' + this.queue.yearSelection.from + ' bis ' + this.queue.yearSelection.to;
                    queueData = {
                        type: 'year',
                        fromYear: this.queue.yearSelection.from,
                        toYear: this.queue.yearSelection.to,
                        shuffle: random
                    };
                    break;
                case QUEUE_MODE_ALBUM:
                    if (this.queue.albumSelection.albumList.length > 1) {
                        defaultTitle = this.queue.albumSelection.albumList.length + " Alben";
                    }else{
                        defaultTitle = "1 Album";
                    }
                    queueTitle = (this.queue.albumSelection.title === '' ? defaultTitle: this.queue.albumSelection.title);
                    queueData = {
                        type: 'albums',
                        albumList: this.queue.albumSelection.albumList,
                        shuffle: random,
                        title: queueTitle
                    };
                    break;
            }
            this.loading = true;
            this.hideQueueModal();
            axios.post('/api/queue/create', queueData)
                .then(() => {
                    this.errorMessage = '';
                    this.successMessage = 'Eine Warteschlange mit dem Titel "' + queueTitle + '" wurde erfolgreich erstellt.';
                    this.triggerActionQueue(queueTitle);
                })
                .catch(error => {
                    console.log(error)
                    this.errorMessage = 'Die Warteschlange konnte nicht erstellt werden. Bitte probieren Sie es erneut. Kontaktieren Sie ansonsten Ihren Administrator.'
                })
                .finally(res => {
                    this.loading = false;
                });
        },
        abortQueue() {
            this.hideQueueModal();
        },
        clearData() {
            this.queue.albumSelection = [];
            this.selectLatestYears();
            this.albumSelectorKey=!this.albumSelectorKey;
        },
        showQueueModal() {
            this.$refs['queue-order-modal'].show();
        },
        hideQueueModal() {
            this.$refs['queue-order-modal'].hide()
        },
        showSettings() {
            this.settingsVisible = true;
        },
        saveSettings() {
            this.triggerActionSettingDuration(this.slideshow.duration).then(() => {
                this.successMessage = 'Die Geschwindigkeit der Slideshow wurd auf ' + this.durationOutput + ' gesetzt';
            });
        },
        deletePicture() {
            this.loading = true;
            axios.delete('api/index/' + this.queue.statistics.index_id).then(() => {
                this.successMessage = 'Das Foto wurde erfogreich gelöscht.';
                this.triggerActionSimple(SLIDESHOW_ACTION_RELOAD_CURRENT);
                this.$refs['delete-picture-modal'].hide();
                this.loading = false;
            })
        },
        toggleFavorite() {
            this.loading = true;
            axios.put('api/index/toggleFavorite/' + this.queue.statistics.index_id).then(() => {
                this.queue.statistics.favorite = !this.queue.statistics.favorite;
                this.loading = false;
                if (this.queue.statistics.favorite) {
                    this.successMessage = 'Das Foto wurde erfogreich als Favorit markiert.';
                    this.triggerActionSimple(SLIDESHOW_ACTION_ADD_FAVORITE);
                }else{
                    this.triggerActionSimple(SLIDESHOW_ACTION_REMOVE_FAVORITE);
                    this.successMessage = 'Das Foto ist nun kein Favorit mehr. Es kann jederzeit wieder als Favorit markiert werden';
                }
            })
        }
    }
};
</script>

<style scoped lang="scss">
@import "../../sass/variables";

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
        z-index: 100;
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

    .album-selector .card-body {
        padding: 0.6em;
    }

    .input-group > .custom-range {
        background-color: $progress-bg;
        border: none;
    }
</style>
