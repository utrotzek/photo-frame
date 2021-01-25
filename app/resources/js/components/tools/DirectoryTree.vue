<template>
        <div class="directory-tree tree-entry" :class="{selected: node.selected, 'child-selected': childSelected}"
    >
        <div>
            <div class="title-wrapper">
                <div class="icon" :class="indent" @click="toggleChildren">
                    <span v-if="nodes">
                        <b-icon-plus-circle-fill class="mr-2" v-if="!showChildren"></b-icon-plus-circle-fill>
                        <b-icon-dash-circle-fill class="mr-2" v-else></b-icon-dash-circle-fill>
                    </span>
                </div>
                <div class="title" :class="indent" @click="toggleChildren">
                    {{ title }}
                </div>
                <div class="selection" @click="toggleSelection">
                    <b-icon-circle v-if="!node.selected"></b-icon-circle>
                    <b-icon-check2-circle v-else></b-icon-check2-circle>
                </div>
            </div>
            <directory-tree
                v-if="showChildren"
                v-for="node in nodes"
                :key="node.path"
                :node="node"
                :nodes="node.nodes"
                :selected="node.selected"
                :title="node.title"
                :depth="depth + 1"
                @node-selected="nodeSelected"
            >
            </directory-tree>
        </div>
    </div>
</template>

<script>
export default {
    name: "directory-tree",
    props: ['title', 'nodes', 'node', 'depth', 'selected'],
    computed: {
        indent() {
            return 'ml-'+this.depth * 2;
        },
        childSelected() {
            if (!this.node.selected && Array.isArray(this.nodes)) {
                return this.recursiveNodeChildSelected(this.nodes);
            }
            return false;
        }
    },
    data() {
        return {
            showChildren: false,
        }
    },
    methods: {
        toggleChildren() {
            this.showChildren= !this.showChildren;
        },
        toggleSelection() {
            this.$emit("node-selected", this.node.path, !this.node.selected)
        },
        nodeSelected(path, selected){
            this.$emit('node-selected', path, selected)
        },
        recursiveNodeChildSelected(nodes){
            let i=0;
            if (Array.isArray(nodes)){
                for(i=0; i < nodes.length;i++){
                    let item=nodes[i];

                    if (item.selected){
                        return true;
                    }else{
                        if (Array.isArray(item.nodes)){
                            if (this.recursiveNodeChildSelected(item.nodes)){
                                return true;
                            }
                        }
                    }
                }
            }
            return false;
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
        width: 100%;
        position: relative;
        cursor: pointer;
        border-bottom: 1px solid white;
    }
    .tree-entry .title,
    .tree-entry .selection {
        display: inline-block;
        padding: 0.7em;
    }

    .tree-entry .selection {
        font-size: 1.2em;
        width: 2em;
        position: absolute;
        right: 0;
        top: 0
    }

    .tree-entry .icon {
        position: absolute;
        left:7px;
        top:14px;
    }

    .tree-entry .title {
        font-size: 1.2em;
        margin-right: 2em;
        padding-left: 2em;
    }

    .tree-entry {
        color: white;
        background-color: $dark;
    }

    .tree-entry.selected{
        background-color: $success;
    }

    .tree-entry.child-selected{
        background-color: $yellow;
    }
</style>
