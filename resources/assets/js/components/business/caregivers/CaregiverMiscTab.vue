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
                <custom-field-form @customFields="filterMeta" :user-id="caregiver.id" user-role="caregiver" :meta="caregiver.meta" />
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <b-card header="Audit Log"
                        header-bg-variant="info"
                        header-text-variant="white">
                    <audits-table :trail="auditLogItems"></audits-table>
                </b-card>
            </b-col>
        </b-row>
        <b-row if="isAdmin">
            <b-col>
                <b-card header="Meta Data-Visible to admins only"
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
                metaFields: [
                    { key: 'key', label: 'Key', sortable: true, },
                    { key: 'value', label: 'Value', sortable: true, },
                ],
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
                let response = await axios.get(`/business/reports/audit-log?caregiver_id=${this.caregiver.id}`);
                this.auditLogItems = response.data;
            },
            filterMeta(data){
                this.meta = this.caregiver.meta.filter(item1 =>
                    !data.some(item2 => (item2.key === item1.key && item2.key === item1.key)));
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