<template>
    <b-select :disabled="disabled" v-model="selectedBusiness" v-show="!hidden" :name="name">
        <option v-if="allowAll && sortedBusinesses.length > 1" value="">All Office Locations</option>
        <option v-else value="">--Select an Office Location--</option>
        <option v-for="business in sortedBusinesses" :key="business.id" :value="business.id">
            {{ isOfficeUser ? business.short_name : business.name }}
        </option>
    </b-select>
</template>

<script>
    import { mapState } from 'vuex'
    import AuthUser from '../../mixins/AuthUser';

    export default {
        mixins: [AuthUser],
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
                    if (this.value) {
                        return this.value;
                    }

                    if (this.officeUserSettings) {
                        return this.officeUserSettings.default_business_id;
                    }

                    return this.$store.getters.defaultBusiness.id || "";
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
            sortedBusinesses() {
                return this.isOfficeUser ? _.sortBy(this.businesses, 'short_name') : _.sortBy(this.businesses, 'name');
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
            this.$emit('input', this.selectedBusiness);
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
