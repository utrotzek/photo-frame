<template>
    <div class="directory-tree tree-entry" :class="{selected: selected}"
    >
        <div>
            <div class="title-wrapper">
                <div class="title" :class="indent">
                    <span v-if="nodes" @click="toggleChildren">
                        <b-icon-plus-circle-fill class="mr-2" v-if="!showChildren"></b-icon-plus-circle-fill>
                        <b-icon-dash-circle-fill class="mr-2" v-else></b-icon-dash-circle-fill>
                    </span>
                    <span class="empty-icon mr-2" v-else>&nbsp;</span>
                    {{ title }}
                </div>
                <div class="selection float-right" @click="toggleSelection">
                    <b-icon-circle v-if="!selected"></b-icon-circle>
                    <b-icon-check2-circle v-else></b-icon-check2-circle>
                </div>
            </div>
            <directory-tree
                v-if="showChildren"
                v-for="node in nodes"
                :key="node.path"
                :nodes="node.nodes"
                :title="node.title"
                :depth="depth + 1"
            >
            </directory-tree>
        </div>
    </div>
</template>

<script>
export default {
    name: "directory-tree",
    props: ['title', 'nodes', 'depth'],
    computed: {
        indent() {
            return 'ml-'+this.depth * 2;
        }
    },
    data() {
        return {
            showChildren: false,
            selected: false
        }
    },
    methods: {
        toggleChildren() {
            this.showChildren= !this.showChildren;
        },
        toggleSelection() {
            this.selected= !this.selected;
        }
    }
}
</script>

<style scoped lang="scss">
@import "../../../sass/variables";
    .empty-icon {
        width: 1em;
        height: 1em;
        display: inline-block;
    }
    .tree-entry .title-wrapper {
        cursor: pointer;
        border-bottom: 1px solid white;
    }
    .tree-entry .title,
    .tree-entry .selection {
        display: inline-block;
        padding: 0.7em;
    }

    .tree-entry .selection {
        width: 2em;
    }
    .tree-entry .title {
        margin-right: 2em;
    }

    .tree-entry {
        color: white;
        background-color: $dark;
    }

    .tree-entry.selected{
        background-color: $success;
    }
</style>