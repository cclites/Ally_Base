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
                </b-table>
            </div>
        </div>

    </b-card>
</template>

<script>
    export default {
        name: "TenNinetyNine",
        props: {
          items: []
        },
        data: {
            return: {
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
            }
        },
    }
</script>

<style scoped>

</style>