<template>

    <b-card header="Admin Note"
            header-bg-variant="info"
            header-text-variant="white">

        <div class="d-flex flex-column">

            <div class="note-body">

                <b-form-group label="Admin Note" label-for="notes">

                    <b-form-textarea
                        id="notes"
                        name="notes"
                        :rows="3"
                        v-model="form.admin_note"
                    >
                    </b-form-textarea>
                    <p>Notes for internal admin-use only</p>
                </b-form-group>
            </div>
            <b-form-group label="&nbsp;">
                <b-btn variant="info" class="pull-right" @click="saveNote()" :disabled="form.busy">Save Note</b-btn>
            </b-form-group>
        </div>
    </b-card>
</template>

<script>

    export default {

        props: {

            user: ''
        },

        data() {

            return {

                form : new Form({

                    admin_note : ''
                })
            }
        },

        mounted(){

            if( this.user ) this.form.admin_note = this.user.admin_note;
        },

        methods : {

            saveNote(){

                this.form.patch( `/admin/users/${this.user.id}` )
                    .then( res => {

                        console.log( 'responses: ', res );
                    })
                    .catch( err => {

                        console.log( 'ERRORS!! ', err );
                    })
            }
        }
    }
</script>

<style scoped>

</style>