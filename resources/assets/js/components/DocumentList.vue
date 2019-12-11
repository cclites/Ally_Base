<template>
    <b-card header="Documents"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="upload()" enctype="multipart/form-data" class="d-flex justify-content-between align-items-center">

            <div class="d-flex align-items-center">

                <input type="file" name="document" @change="setFile($event.target.files[0])" required>

                <b-form-input
                    v-model="description"
                    placeholder="File Description..."
                    style="max-width: 200px">
                </b-form-input>
            </div>
            <div>

                <input type="submit" value="Upload" class="btn btn-success ml-3">

                <b-button variant="info" @click=" getDischarge() " v-if="!active" class="ml-3"><i class="fa fa-file mr-1"></i>Download Discharge Summary</b-button>
            </div>
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
                <tr v-for="document in documents" :key="document.id">
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
        props: ['initialDocuments', 'userId', 'active', 'type'],

        data() {
            return {
                documents: _.orderBy(this.initialDocuments, 'updated_at', 'desc'),
                file: {},
                description: ''
            };
        },

        methods: {

            getDischarge(){

                window.open( `/business/${ this.type }s/discharge-letter/${this.userId}` );
            },
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
