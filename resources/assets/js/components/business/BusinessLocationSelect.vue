<template>
    <b-select :disabled="businesses.length < 2" v-model="selectedBusiness">
        <option v-if="allowAll && businesses.length > 1" value="">All Business Locations</option>
        <option v-for="business in businesses" :key="business.id" :value="business.id">
            {{ business.name }}
        </option>
    </b-select>
</template>

<script>
    import { mapState } from 'vuex'

    export default {
        name: "BusinessLocationSelect",

        props: {
            allowAll: Boolean,
        },

        computed: {
            ...mapState({
                businesses: state => state.business.businesses
            }),
            selectedBusiness: {
                get() {
                    return this.value || this.$store.getters.defaultBusiness.id || "";
                },
                set(value) {
                    this.$emit('input', value);
                }
            }
        },

        data() {
            return {
            }
        },

        watch: {

        }
    }
</script>

<style scoped>

</style>