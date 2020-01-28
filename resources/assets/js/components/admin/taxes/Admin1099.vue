<template>
    <b-card
            header="1099"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <div>
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                         :items="items"
                         :fields="fields"
                        sort-by="name"
                         ref="table"
                >
                    <template slot="name" scope="row">
                        <span v-if="row.item.disabled">{{ row.item.name }} (Disabled)</span>
                        <a v-else :href="row.item.url">{{ row.item.name }}</a>
                    </template>
                </b-table>
            </div>

            <div class="table-responsive mt-4">
                <b-table bordered striped hover show-empty
                         :items="otherItems"
                         :fields="fields"
                        sort-by="name"
                         ref="table"
                >
                    <template slot="name" scope="row">
                        <span v-if="row.item.disabled">{{ row.item.name }} (Disabled)</span>
                        <a v-else :href="row.item.url">{{ row.item.name }}</a>
                    </template>
                </b-table>
            </div>
        </div>

    </b-card>
</template>

<script>
    export default {
        name: "TenNinetyNine",
        props: {
        },
        data() {
            return {
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: false,
                    },
                    {
                        key: 'description',
                        label: 'What this does',
                        sortable: false,
                    },
                ],
                items: [
                    {
                        name: '1099 Preview Report',
                        description: 'Review 1099s',
                        url: 'preview-1099-report',
                        disabled: false,
                    },
                    {
                        name: 'Caregivers Not Elected Report',
                        description: "Lists Caregivers eligible for 1099s for Client's not elected to send.",
                        url: 'reports/1099-not-elected',
                        disabled: false,
                    },
                    {
                        name: 'Ally 1099 Payer Info',
                        description: 'Download Transmission Report and Client/CG Emails',
                        url: 'admin-1099',
                        disabled: false,
                    },
                    {
                        name: 'Ally Contact Info',
                        description: 'Edit Ally contact information',
                        url: 'admin-contact-info',
                        disabled: false,
                    },
                    {
                        name: 'Registry Email List',
                        description: 'Display a list of all registry emails',
                        url: 'registry-email-list',
                        disabled: false,
                    },
                    {
                        name: 'Malformed 1099s',
                        description: 'Display malformed 1099s for Registry',
                        url: 'reports/bad-1099-report',
                        disabled: false,
                    },
                ],
                otherItems: [
                    {
                        name: 'Update 1099s',
                        description: 'Send Clients/Caregivers reminders to update 1099s',
                        url: 'update-1099-reminder',
                        disabled: true,
                    },
                    {
                        name: 'ALLY 1099 Payers Preview Report',
                        description: 'Preview Ally Payers',
                        url: '#', //'ally-preview-1099-report',
                        disabled: true,
                    },
                    {
                        name: '1099 Creation',
                        description: 'Artisan command to run that will calculate and populate (append to) a 1099 table',
                        url: '#', //'generate-1099',
                        disabled: true
                    },
                    {
                        name: 'Missing or Malformed SSN for Clients',
                        description: 'Display missing or malformed SSNs for Clients',
                        url: 'reports/bad-ssn-report/clients',
                        disabled: false,
                    },
                    {
                        name: 'Missing or Malformed SSN for Caregivers',
                        description: 'Display missing or malformed SSNs for Caregivers',
                        url: 'reports/bad-ssn-report/caregivers',
                        disabled: false,
                    },
                ],
            }
        },
    }
</script>

<style scoped>

</style>