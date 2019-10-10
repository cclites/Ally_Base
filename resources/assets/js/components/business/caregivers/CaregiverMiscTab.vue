<template>
    <b-card>
        <b-row>
            <b-col>
                <b-card header="Miscellaneous"
                        header-bg-variant="info"
                        header-text-variant="white">
                    <b-form-group>
                        <b-form-textarea v-model="form.misc" rows="3"></b-form-textarea>
                    </b-form-group>
                    <b-form-group>
                        <b-btn @click="updateCaregiver" variant="info">Save</b-btn>
                    </b-form-group>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <custom-field-form :user-id="caregiver.id" user-role="caregiver" :meta="caregiver.meta" />
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
        props: ['misc', 'caregiver'],
        mixins: [ FormatsDates, FormatsStrings],
        components: {AuditsTable},
        data() {
            return{
                form: new Form({
                    misc: this.misc,
                }),
                role: window.AuthUser,
                auditLogItems: [],
                fields: [
                    { label: 'Type', key: 'auditable_title', sortable: true },
                    { label: 'Event', key: 'event', sortable: true, formatter: (val) => this.stringFormat(val) },
                    { label: 'By', key: 'user', sortable: true },
                    { label: 'Date', key: 'updated_at', sortable: true, formatter: (val) => this.formatDateTimeFromUTC(val) },
                    { label: 'Old Values', key: 'old_values', formatter: (val) => JSON.stringify(val) },
                    { label: 'New Values', key: 'new_values', formatter: (val) => JSON.stringify(val) },
                ],
                sortBy: '',
                emptyText: 'No records to display',
                meta: [],
                item: [],
            };
        },
        async mounted() {
            this.fetchAuditLog();
        },

        methods: {
            updateCaregiver() {
                this.form.put(`/business/caregivers/${this.caregiver.id}/misc`)
                    .then(response => {
                    })
                    .catch(() => {});
            },
            async fetchAuditLog(){
                axios.get(`/business/reports/audit-log?caregiver_id=${this.caregiver.id}`)
                    .then( ({ data }) => {
                        this.auditLogItems = data;
                    })
                    .catch(() => {});
            },
        },
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
        overflow: auto;
    }
</style>