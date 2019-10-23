<template>
    <b-card>
        <b-row>
            <b-col>
                <custom-field-form :businessId=" client.business_id " :user-id="client.id" user-role="client" :meta="client.meta" />
            </b-col>
        </b-row>
        <audits-table :trail="auditLogItems"></audits-table>
    </b-card>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import AuditsTable from '../../../components/AuditsTable';

    export default {
        mixins: [ FormatsDates, FormatsStrings],
        components: {AuditsTable},
        props: {
            client: {
                type: Object,
                required: true,
            }
        },
        data() {
            return {
                auditLogItems: [],
                fields: [
                    { label: 'Type', key: 'auditable_title', sortable: true, formatter: (val) => this.stringFormat(val) },
                    { label: 'Event', key: 'event', sortable: true, formatter: (val) => this.stringFormat(val) },
                    { label: 'By', key: 'user', sortable: true },
                    { label: 'Date', key: 'updated_at', sortable: true, formatter: (val) => this.formatDateTimeFromUTC(val) },
                    { label: 'Old Values', key: 'old_values', formatter: (val) => JSON.stringify(val) },
                    { label: 'New Values', key: 'new_values', formatter: (val) => JSON.stringify(val) },
                ],
                sortBy: '',
                emptyText: 'No records to display',
                item: [],
            }
        },
        async mounted() {
            this.fetchAuditLog();
        },

        methods: {
            async fetchAuditLog(){
                axios.get(`/business/reports/audit-log?client_id=${this.client.id}`)
                    .then( ({ data }) => {
                        this.auditLogItems = data;
                    })
                    .catch(() => {});
            },
        }
    }
</script>

<style scoped>
    table.table tbody tr td{
        overflow-wrap: break-all;
    }

    .table-wrapper{
        max-height: 800px;
        overflow-y: auto;
    }

    table.table{
        table-layout: fixed;
    }
</style>