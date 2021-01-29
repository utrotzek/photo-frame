<template>
    <div class="album-selector pr-3 pl-3">
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
                <b-button block variant="primary" class="mt-2" @click="select">Los</b-button>
            </b-col>
            <b-col>
                <b-button block variant="secondary" class="mt-2" @click="loadAlbumData">Auswahl löschen</b-button>
            </b-col>
        </b-row>
    </div>
</template>

<script>
import DirectoryTree from "./DirectoryTree";
export default {
    name: 'album-selector',
    components: {DirectoryTree},
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
            }
        }
    },
    mounted() {
       this.loadAlbumData();
    },
    methods: {
        loadAlbumData() {
            axios.get('/api/index/directories').then((res) => {
                this.albums = res.data;
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
        }
    }
}
</script>

<style scoped>

</style>
