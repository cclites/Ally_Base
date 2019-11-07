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
                         ref="table"
                >
                    <template slot="name" scope="row">
                        <a :href="row.item.url">{{ row.item.name }}</a>
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
                        sortable: true,
                    },
                    {
                        key: 'description',
                        label: 'What this does',
                        sortable: true,
                    },
                ],
                items: [
                    {
                        name: 'Registry Email List',
                        description: 'Display a list of all registry emails',
                        url: 'registry-email-list'
                    },
                    {
                        name: 'Missing or Malformed SSN for Clients',
                        description: 'Display missing or malformed SSNs for Clients',
                        url: 'reports/bad-ssn-report/clients'
                    },
                    {
                        name: 'Missing or Malformed SSN for Caregivers',
                        description: 'Display missing or malformed SSNs for Caregivers',
                        url: 'reports/bad-ssn-report/caregivers'
                    },

                ],
            }
        },
    }
</script>

<style scoped>

</style>