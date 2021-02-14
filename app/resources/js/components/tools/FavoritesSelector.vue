<template>
    <div id="favorites-selector">
        <b-row>
            <b-col>
                Aktuell sind <b>{{ favoritesCount }}</b> als Favoriten markiert. Sie können die Favoriten mit einem Klick auf
                diesen Button abspielen.
            </b-col>
        </b-row>
        <b-row class="mt-4">
            <b-col class="text-center">
                <b-button variant="primary" @click="$emit('start-favorites')">
                    <b-icon-image></b-icon-image> {{ favoritesCount }} Favoriten abspielen
                </b-button>
            </b-col>
        </b-row>

    </div>
</template>

<script>
export default {
    name: "FavoritesSelector",
    data() {
        return {
            favoritesCount: 0,
            errorMessage: ''
        }
    },
    mounted() {
        axios.get('/api/index/favorites').then((res) => {
            this.favoritesCount = res.data;
        }).catch((error) => {
            this.errorMessage = "Fehler beim Laden der Favoriten."
        })
    }
}
</script>

<style scoped>

</style>
