<template>
    <b-select :disabled="disabled" v-model="selectedBusiness" v-show="!hidden" :name="name">
        <option v-if="allowAll && businesses.length > 1" value="">All Business Locations</option>
        <option v-else value="">--Select a Business Location--</option>
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
            value: '',
            allowAll: Boolean,
            hideable: Boolean, // Hide when there is only one element
            name: String,
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
            },
            disabled() {
                return this.businesses.length < 2;
            },
            hidden() {
                return this.hideable && this.disabled;
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