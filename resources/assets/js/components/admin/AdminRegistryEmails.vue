<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Registry Contact Emails"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form-group label="" label-for="emails">
                        <b-textarea
                                id="emails"
                                name="emails"
                                :rows="3"
                                v-model="emails"
                        >
                        </b-textarea>
                    </b-form-group>

                    <b-col>
                        <b-button variant="info"
                                  @click="copy()"
                                  class="mb-2 float-right"
                                  title="Copy to Clipboard">
                            <i class="fa fa-copy"></i>Copy to Clipboard</b-button>
                    </b-col>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    export default {
        name: "AdminRegistryEmails",
        props: {
            emails: ''
        },
        methods: {
            copy(){
                var copyText = document.querySelector("#emails");
                copyText.select();
                document.execCommand("copy");
            }
        }
    }
</script>

<style scoped>

</style>