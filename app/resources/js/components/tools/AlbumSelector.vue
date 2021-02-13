<template>
    <div class="album-selector">
        <b-row>
            <b-col>
                <search @searched="searched" ></search>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <directory-tree
                    :nodes="node.nodes"
                    :node="node"
                    :title="node.title"
                    :depth="0"
                    v-for="node in albums.nodes"
                    :key="node.path"
                    @node-selected="nodeSelected"
                ></directory-tree>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-button-group class="d-flex album-button-group">
                    <b-button block variant="primary" class="mt-2 mx-1" @click="select">
                        <b-icon-hand-thumbs-up></b-icon-hand-thumbs-up>
                        <span class="d-none d-md-inline">Los</span>
                    </b-button>
                    <b-button block variant="primary" class="mt-2 mx-1" @click="$refs['save-as-playlist-modal'].show()" v-if="playlistSaveEnabled">
                        <b-icon-plus-circle-fill></b-icon-plus-circle-fill>
                        <span class="d-none d-md-inline">Als Playliste</span>
                    </b-button>
                    <b-button block variant="danger" class="mt-2 mx-1" @click="loadAlbumData">
                        <b-icon-trash2></b-icon-trash2>
                        <span class="d-none d-md-inline">Abbrechen</span>
                    </b-button>
                </b-button-group>
            </b-col>
        </b-row>

        <b-modal id="save-as-playlist-modal" ref="save-as-playlist-modal" title="Auswahl als Playlist speichern" hide-footer centered>
            <p >Sie können die aktuelle Auswahl als Playlist speichern. Geben Sie dazu einfach den gewünschten Namen der Playlist ein:</p>
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
                            v-model="playlistTitle"
                            type="text"
                            required
                            block
                        ></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col class="text-right">
                    <b-button-group class="mt-3">
                        <b-button
                            @click="savePlaylist"
                            variant="primary"
                            class="mr-1"
                        >
                            Speichern
                        </b-button>
                        <b-button @click="$refs['save-as-playlist-modal'].hide()">Abbrechen</b-button>
                    </b-button-group>
                </b-col>
            </b-row>
        </b-modal>
    </div>
</template>

<script>
import DirectoryTree from "./DirectoryTree";
import Search from "./Search";

export default {
    name: 'album-selector',
    components: {DirectoryTree, Search},
    props: {
        playlistSaveEnabled: {
            type: Boolean,
            default: true
        },
        selectedPaths: Array
    },
    data () {
        return {
            albums: {
                nodes: [
                    {
                        title: '2020',
                        path: '/2020',
                        selected: false,
                        nodes: [
                            {
                                path: '/2020/Urlaub_Aegypten',
                                title: 'Urlaub Ägypten',
                                selected: false,
                            }
                        ]
                    },
                    {
                        path: '/2019',
                        title: '2019',
                        selected: false,
                    }
                ]
            },
            playlistTitle: ''
        }
    },
    mounted() {
        this.loadAlbumData().then(() => {
            if (Array.isArray(this.selectedPaths)){
                this.selectNodesByPathList(this.selectedPaths);
            }
        });
    },
    methods: {
        loadAlbumData() {
            return new Promise((resolve, reject) => {
                axios.get('/api/index/directories').then((res) => {
                    this.albums = res.data;
                    resolve(this.albums);
                }).catch((error) => {
                    reject(error);
                });
            });
        },
        nodeSelected(path, selected) {
            this.updateNodesRecursively(this.albums.nodes, path, selected);
        },
        updateNodesRecursively(nodes, selectedPath, selected) {
            if (Array.isArray(nodes)) {
                nodes.forEach((item) => {
                    if (Array.isArray(item.nodes)) {
                        this.updateNodesRecursively(item.nodes, selectedPath, selected);
                    }
                    if (item.path === selectedPath) {
                        item.selected = selected;
                        this.setSelectedRecursively(item.nodes, selected);
                    }
                })
            }
        },
        setSelectedRecursively(nodes, selected){
            if (Array.isArray(nodes)){
                nodes.forEach((item) => {
                    if (Array.isArray(item.nodes)) {
                        this.setSelectedRecursively(item.nodes, selected);
                    }
                    item.selected = selected;
                });
            }
        },
        select(){
            const pathList = this.recursiveSelectPathList(this.albums.nodes);
            this.$emit('selected', pathList)
        },
        recursiveSelectPathList(nodes) {
            let selectedList = [];

            nodes.forEach((item) => {
                if (item.selected){
                    selectedList.push(item.path)
                }else if(Array.isArray(item.nodes)){
                    selectedList = selectedList.concat(this.recursiveSelectPathList(item.nodes))
                }
            });
            return selectedList;
        },
        searched(termin) {
            if (Array.isArray(this.albums.nodes)){
                this.showFoundAlbumsRecursive(this.albums.nodes, termin);
            }
        },
        //searches recursively for the given title and sets the "visible" attribute for the node
        showFoundAlbumsRecursive(nodes, term){
            let found = false, anyFound = 0, i = 0, foundSub = false;

            const reg = new RegExp(".*" + term + ".*", "gi");

            nodes.forEach((item) => {
                if (term === ""){
                    found = true;
                }
                if (Array.isArray(item.nodes)){
                    foundSub = this.showFoundAlbumsRecursive(item.nodes, term);
                }

                if (foundSub){
                    found = true;
                }else {
                    found = !!item.title.match(reg);
                }

                if (found && !anyFound){
                    anyFound = true;
                }
                item.visible = found;
            });
            return anyFound;
        },
        selectNodesByPathList(pathList) {
            pathList.forEach((item) => {
                this.updateNodesRecursively(this.albums.nodes, item.path, true);
            })
        },
        savePlaylist(){
            const pathList = this.recursiveSelectPathList(this.albums.nodes);

            this.$emit('playlist-saved', this.playlistTitle, pathList);
            this.$refs['save-as-playlist-modal'].hide();
            this.playlistTitle = "";
            this.loadAlbumData();
        }
    }
}
</script>

<style scoped>
</style>
