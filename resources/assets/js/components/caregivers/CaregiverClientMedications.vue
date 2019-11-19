<template>
    <b-card
        header="Medications"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <ally-table id="client-medication" :columns="fields" :items="items" sort-by="">
        </ally-table>
    </b-card>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';

    export default {
        props: {
            client: {
                type: Object,
                required: true,
            },
            medications: {
                type: Array,
                required: true,
            },
        },

        mixins: [FormatsDates],

        data() {
            return {
                editModal: false,
                confirmDeleteModal: false,
                submitting: false,
                selected: {},
                fields: [
                    {
                        key: 'type',
                        label: 'Name',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'dose',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'frequency',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'description',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'route',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        label: 'New/Changed',
                        key: 'new_changed',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'tracking',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'side_effects',
                        sortable: false,
                        shouldShow: true,
                    },
                    {
                        key: 'notes',
                        sortable: false,
                        shouldShow: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print',
                        sortable: false,
                        shouldShow: true,
                    },
                ],
                items: this.medications,
            }
        },

        computed: {
            modalTitle() {
                const action = this.selected.id ? 'Edit' : 'Add a New';
                return `${action} Medication`;
            },
        },

        methods: {
        }
    }
</script>

<style>
</style>