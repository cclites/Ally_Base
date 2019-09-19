<template>
    <b-form-select v-model="selectedPayer" @input="onChange()">
        <option v-if="loading" selected value="">{{ loadingText }}</option>
        <option v-else value="">{{ emptyText }}</option>
        <option value="0" v-if="showClient">{{ clientDisplay }}</option>
        <option value="1" v-if="showOffline">{{ offlineDisplay }}</option>
        <option v-for="item in payers" :key="item.id" :value="`${item.id}`">
            {{ item.name }}
        </option>
    </b-form-select>
</template>

<script>
    import { mapGetters } from 'vuex';
    export default {
        props: {
            /**
             * The text to display for the Loading item.
             */
            loadingText: {
                type: String,
                default: 'Loading...',
            },
            /**
             * The text to display for the Empty item.
             */
            emptyText: {
                type: String,
                default: '-- Select a Payer --',
            },
            /**
             * The text to display for 'Private Pay' Payers.
             */
            clientDisplay: {
                type: String,
                default: '(Client)',
            },
            /**
             * Whether or not to show Private Pay/Client Option.
             */
            showClient: {
                type: Boolean,
                default: true,
            },
            /**
             * The text to display for 'OFFLINE' Payer.
             */
            offlineDisplay: {
                type: String,
                default: '(Offline)',
            },
            /**
             * Whether or not to show OFFLINE Option.
             */
            showOffline: {
                type: Boolean,
                default: false,
            },
            /**
             * The starting selected value.
             */
            value: {
                type: String,
                default: '',
            },
        },

        computed: {
            ...mapGetters({
                loading: 'filters/isPayersLoading',
                payers: 'filters/payerList',
            }),
        },

        data() {
            return {
                selectedPayer: '',
            };
        },

        methods: {
            onChange() {
                this.$emit('input', this.selectedPayer);
            },
        },

        watch: {
            selectedPayer(newValue, oldValue) {
            },

            value(newValue, oldValue) {
                this.selectedPayer = newValue;
            },
        },

        created() {
            this.selectedPayer = this.value;
        },

        async mounted() {
            await this.$store.dispatch('filters/fetchPayers');
        },
    }
</script>
