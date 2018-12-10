<template>
    <small class="form-text text-muted">
        <span v-if="form.hasError(field)" v-text="form.getError(field)" class="text-danger"></span>
        <span v-else v-text="text"></span>
    </small>
</template>

<script>
    export default {
        props: {
            'form': {},
            'field': '',
            'text': '',
        },
        watch: {
            'form.errorMods': function() {
                this.$forceUpdate();
            }
        }
    }
</script>

<style scoped>
    .text-danger {
        color: red !important;
    }
    .text-warning {
        color: orange !important;
    }
    .text-info {
        color: blue !important;
    }
</style>