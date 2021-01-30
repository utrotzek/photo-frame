<template>
    <div class="search-wrapper">
        <!-- Search form -->
        <b-form
            class="search-form form-inline md-form form-sm mt-0"
            @submit="onSubmit"
        >
            <b-icon-search class="search-icon">
            </b-icon-search>
            <b-input
                v-model="query"
                class="search-control"
                type="text"
                placeholder="Suche"
                aria-label="Search"
                @keyup="triggerSearch"
            >
            </b-input>
            <span
                class="search-clear"
                @click="clearQuery"
                v-if="displayDelete"
            >
                <b-icon-x-circle-fill></b-icon-x-circle-fill>
            </span>
        </b-form>
    </div>
</template>

<script>
export default {
    name: "SearchVue",
    data() {
        return {
            query: "",
            displayDelete: false
        };
    },
    methods: {
        onSubmit(e) {
            /* istanbul ignore next */
            e.preventDefault();
        },
        triggerSearch() {
            this.displayDelete = this.query.length > 0;
            this.$emit("searched", this.query);
        },
        clearQuery() {
            this.query = "";
            this.triggerSearch();
        }
    }
};
</script>

<style scoped lang="scss">
@import "../../../sass/variables";

.search-wrapper {
    position: relative;
}
.search-control {
    padding: 0 30px 0 30px;
    height: 2.5rem;
}
.search-clear {
    position: absolute;
    display: inline-block;
    right: 0;
    top: 0;
    color: #868e96;
    padding-top: 0.5rem;
    padding-left: 0.5rem;
    cursor: pointer;
    z-index: 100;
    height: 100%;
    width: 30px;
    align-content: center;
}
.search-icon {
    position: absolute;
    left: 0.5rem;
    top: 0.8rem;
    color: $gray-500;
}
</style>
