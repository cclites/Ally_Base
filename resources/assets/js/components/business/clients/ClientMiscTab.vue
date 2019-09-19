<template>
    <b-card>
        <b-row>
            <b-col>
                <custom-field-form :user-id="client.id" user-role="client" :meta="client.meta" />
            </b-col>
        </b-row>
        <h4>Audit Log</h4>
        <b-row>
            <b-col md-12>
                <b-table
                        class="log-table"
                        :items="auditLogItems"
                        :fields="fields"
                        :sort-by="sortBy"
                        :empty-text="emptyText"
                >
                    <template slot="user" scope="row">
                        {{ row.item.user.nameLastFirst }}
                    </template>
                </b-table>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>

    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsStrings from "../../../mixins/FormatsStrings";

    export default {
        mixins: [ FormatsDates, FormatsStrings],
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
                emptyText: 'No records to display'
            }
        },
        async mounted() {
            this.fetchAuditLog();
        },
        methods: {
            async fetchAuditLog(){
                let response = await axios.get(`/business/reports/audit-log?client_id=${this.client.id}`);
                this.auditLogItems = response.data;
            }
        }
    }
</script>

<style scoped>
    table.table tbody tr td{
        overflow-wrap: break-all;
    }

    table.table{
        overflow: auto;
        width: 100vw;
        table-layout: fixed;
    }
</style>