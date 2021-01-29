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
            <b-icon-x-circle-fill
                v-if="displayDelete"
                class="search-clear"
                @click="clearQuery"
            ></b-icon-x-circle-fill>
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

<style scoped>
.search-wrapper {
    position: relative;
}
.search-control {
    padding: 0 30px 0 30px;
}
.search-clear {
    position: absolute;
    right: 10px;
    top: 10px;
    color: #868e96;
    cursor: pointer;
    z-index: 100;
}
.search-icon {
    position: absolute;
    left: 10px;
    top: 7px;
    color: #ced4da;
}
</style>
