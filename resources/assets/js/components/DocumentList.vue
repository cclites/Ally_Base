<template>
    <b-card header="Documents"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="upload()" enctype="multipart/form-data" class="form-inline">
            <div class="form-group">
                <input type="file" name="document" @change="setFile($event.target.files[0])">
            </div>

            <b-form-group class="ml-2">
                <b-form-input
                    v-model="description"
                    placeholder="File Description...">
                </b-form-input>
            </b-form-group>

            <input type="submit" value="Upload" class="btn btn-success">
        </form>
        <hr>
        <table class="table">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="document in documents">
                    <td>
                        <a :href="'/business/documents/'+document.id+'/download'">
                            {{ document.original_filename }}
                        </a>
                    </td>
                    <td>
                        {{ document.description }}
                    </td>
                    <td>
                       <b-button @click="destroy(document.id)" variant="danger">
                           <i class="fa fa-times"></i>
                       </b-button>
                    </td>
                </tr>
            </tbody>
        </table>
    </b-card>
</template>
<script>
    import FormDataForm from '../classes/FormDataForm';
    import _ from 'lodash';
    export default {
        props: ['initialDocuments', 'userId'],

        data() {
            return {
                documents: _.orderBy(this.initialDocuments, 'updated_at', 'desc'),
                file: {},
                description: ''
            };
        },

        methods: {

            setFile(file) {
                this.file = file;
            },

            upload() {
                let formData = new FormData();
                formData.append('file', this.file);
                formData.append('user_id', this.userId);
                formData.append('description', this.description);
                let form = new FormDataForm(formData);
                form.setOptions({
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                form.post('/business/documents').then(this.refreshList);
            },

            refreshList() {
                axios.get('/business/users/' + this.userId + '/documents')
                    .then((response) => {
                        this.documents = _.orderBy(response.data, 'updated_at', 'desc');
                    });
            },

            destroy(id) {

                if (confirm('Are you sure you want to delete this document?')) {
                    axios.delete('/business/documents/' + id)
                        .then(response => {
                            this.refreshList();
                        })
                        .catch(error => {
                            console.error(error.response);
                        });
                }
            }
        },
    }
</script>
