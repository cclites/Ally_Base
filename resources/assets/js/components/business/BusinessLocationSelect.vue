<template>
    <b-select :disabled="disabled" v-model="selectedBusiness" v-show="!hidden" :name="name">
        <option v-if="allowAll && businesses.length > 1" value="">All Office Locations</option>
        <option v-else value="">--Select a Office Location--</option>
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
            },
        },

        data() {
            return {
            }
        },

        methods: {
            emitLocationCount() {
                // Can be caught by parent component to decide how to display to single or multi-location registries
                this.$emit('locationCount', this.businesses.length);
            }
        },

        mounted() {
            this.emitLocationCount();
        },

        watch: {
            businesses() {
                this.emitLocationCount();
            }
        }
    }
</script>

<style scoped>

</style>