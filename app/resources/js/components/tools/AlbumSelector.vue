<template>
    <div class="album-selector">
        <directory-tree
            :nodes="node.nodes"
            :node="node"
            :title="node.title"
            :depth="0"
            v-for="node in albums.nodes"
            :key="node.path"
            @node-selected="nodeSelected"
        ></directory-tree>
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
    methods: {
        nodeSelected(path, selected) {
            console.log("selected:" + path);
            this.updateNodesRecursively(this.albums.nodes, path, selected);
        },
        updateNodesRecursively(nodes, selectedPath, selected) {
            nodes.forEach((item) => {
                if (item.nodes){
                    this.updateNodesRecursively(item.nodes, selectedPath, selected);
                }
                if (item.path === selectedPath) {
                    item.selected = selected;
                    if (item.nodes){
                        this.setSelectedRecursively(item.nodes, selected);
                    }
                }
            })
        },
        setSelectedRecursively(nodes, selected){
            nodes.forEach((item) => {
                if (item.nodes) {
                    this.setSelectedRecursively(item.nodes, selected);
                }
                item.selected = selected;
            });
        }
    }
}
</script>

<style scoped>

</style>
