<template>
    <div class="album-selector">
        <directory-tree
            :nodes="node.nodes"
            :title="node.title"
            :depth="0"
            v-for="node in albums.nodes"
            :key="node.path"
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
                        nodes: [
                            {
                                path: '/2020/Urlaub_Aegypten',
                                title: 'Urlaub Ägypten'
                            }
                        ]
                    },
                    {
                        path: '/2019',
                        title: '2019'
                    }
                ]
            }
        }
    }
}
</script>

<style scoped>

</style>
