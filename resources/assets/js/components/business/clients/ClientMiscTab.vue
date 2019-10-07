<template>
    <b-card>
        <b-row>
            <b-col>
                <custom-field-form @customFields="filterMeta" :user-id="client.id" user-role="client" :meta="client.meta" />
            </b-col>
        </b-row>
        <audits-table :trail="auditLogItems"></audits-table>
        <b-row if="isAdmin">
            <b-col>
                <b-card header="Meta Data-visible to admins only"
                        header-bg-variant="warning"
                        header-text-variant="white">

                    <b-table bordered striped hover show-empty
                             :items="meta"
                             :fields="metaFields">
                    </b-table>
                </b-card>
            </b-col>
        </b-row>
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
                metaFields: [
                    { key: 'key', label: 'Key', sortable: true, },
                    { key: 'value', label: 'Value', sortable: true, },
                ],
                sortBy: '',
                emptyText: 'No records to display',
                meta: [],
                item: [],
            }
        },
        async mounted() {
            this.fetchAuditLog();


        },
        methods: {
            async fetchAuditLog(){
                let response = await axios.get(`/business/reports/audit-log?client_id=${this.client.id}`);
                this.auditLogItems = response.data;
            },

            filterMeta(data){
                this.meta = this.client.meta.filter(item1 =>
                    !data.some(item2 => (item2.key === item1.key && item2.key === item1.key)));
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