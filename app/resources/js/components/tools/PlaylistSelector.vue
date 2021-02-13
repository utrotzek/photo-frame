<template>
    <div class="playlist-selector">
        <b-row>
            <b-col cols="10">
                <h3>Playlist auswählen</h3>
            </b-col>
            <b-col cols="2" class="text-right">
               <b-button @click="loadPlaylists"><b-icon-arrow-counterclockwise></b-icon-arrow-counterclockwise></b-button>
            </b-col>
        </b-row>
        <b-table :items="items" :fields="fields" :busy="isBusy" striped>
            <template #cell(actions)="row">
                <b-button variant="primary" size="sm" class="mr-1" @click="startPlaylist(row.item)">
                    <b-icon-image></b-icon-image>
                </b-button>
                <b-button size="sm" class="mr-1">
                    <b-icon-pen @click="edit(row.item)"></b-icon-pen>
                </b-button>
                <b-button size="sm" class="mr-1" @click="deletePlaylistModal(row.item)">
                    <b-icon-trash></b-icon-trash>
                </b-button>
            </template>
        </b-table>


        <!-- Modals -->
        <b-modal id="delete-playlist-modal" ref="delete-playlist-modal" title="Playlist wirklich löschen?" hide-footer centered>
            <p class="my-4">Möchten Sie die Playlist wirklich löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.</p>
            <b-row>
                <b-col class="text-right">
                    <b-button-group>
                        <b-button @click="deletePlaylist" variant="danger">Löschen</b-button>
                        <b-button @click="$refs['delete-playlist-modal'].hide()">Abbrechen</b-button>
                    </b-button-group>
                </b-col>
            </b-row>
        </b-modal>

        <b-modal id="edit-playlist-modal" ref="edit-playlist-modal" :title="editTitle" hide-footer centered>
            <p class="my-4">Sie haben hier die Möglichkeit die Playlist zu bearbeiten. Wenn Sie fertig sind, klicken Sie bitte auf speichern.</p>
            <b-row v-if="playlistItemToEdit">
                <b-col>
                    <b-row>
                        <b-col>
                            <b-form-group
                                id="playlist-title-label"
                                label="Playlist Titel"
                                label-for="playlist-title"
                                description="Unter diesem Titel wird die playlist auffindbar sein"
                            >
                                <b-form-input
                                    id="playlist-title"
                                    v-model="playlistItemToEdit.name"
                                    type="text"
                                    block
                                    required
                                    :state="playlistTitleValid"
                                ></b-form-input>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col>
                            <h3>Album Auswahl</h3>
                            <AlbumSelector
                                :selected-paths="playlistItemToEdit.items"
                                :playlist-save-enabled="false"
                                @selected="save"
                            >
                            </AlbumSelector>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
        </b-modal>

    </div>
</template>

<script>
import AlbumSelector from "./AlbumSelector";

export default {
    data() {
        return {
            isBusy: false,
            playlistTitleValid: null,
            fields: [
                {
                    key: 'name',
                    label: 'Name',
                    sortable: true
                },
                {
                    key: 'images',
                    label: 'Bilder',
                    sortable: true
                },
                {
                    key: 'actions',
                    label: 'Aktionen',
                    class: 'actions'
                }
            ],
            items: [
            ],
            playlistItemToDelete: null,
            playlistItemToEdit: null
        }
    },
    components: {AlbumSelector},
    mounted() {
        this.loadPlaylists();
    },
    computed: {
        editTitle() {
            if (this.playlistItemToEdit) {
                return "Playlist " + this.playlistItemToEdit.name + " bearbeiten";
            } else {
                return "";
            }
        }
    },
    methods: {
        loadPlaylists() {
            this.isBusy = true;
            axios.get('/api/playlists').then((res) => {
                this.items = res.data;
                this.isBusy = false;
            }).catch((error) => {
                this.isBusy = false;
            });
        },
        deletePlaylistModal(playlistItem) {
            this.playlistItemToDelete = playlistItem;
            this.$refs['delete-playlist-modal'].show()
        },
        deletePlaylist() {
            const playlistItem = this.playlistItemToDelete;
            axios.delete('/api/playlists/' + playlistItem.id).then((res) => {
                this.$emit('deleted', playlistItem);
                this.loadPlaylists();
                this.$refs['delete-playlist-modal'].hide()
            });
        },
        startPlaylist(playlistItem) {
            this.$emit('start-playlist', playlistItem);
        },
        edit(playlist){
            this.playlistItemToEdit = playlist;
            this.$refs['edit-playlist-modal'].show();
        },
        save(pathList) {
            if (this.playlistItemToEdit.name === "") {
                this.playlistTitleValid = false;
            } else {
                const playlistSaved = _.clone(this.playlistItemToEdit);
                playlistSaved.items = pathList;
                this.$emit('saved', playlistSaved);
                this.$refs['edit-playlist-modal'].hide();
            }
        }
    }
}
</script>

<style scoped>

</style>
<style>
    table .actions {
        width:25%;
        min-width: 12em;
    }

    table th.actions {
        display: none;
    }
</style>
