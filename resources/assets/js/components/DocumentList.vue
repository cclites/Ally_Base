<template>
    <b-card header="Documents"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="upload()" enctype="multipart/form-data" class="form-inline">
            <div class="form-group">
                <input type="file" name="document" @change="setFile($event.target.files[0])">
            </div>
            <input type="submit" value="Upload" class="btn btn-success">
        </form>
        <hr>
        <table class="table">
            <thead><tr><th>File</th></tr></thead>
            <tbody>
                <tr v-for="document in documents">
                    <td>
                        <a :href="'/business/documents/'+document.id+'/download'">
                            {{ document.original_filename }}
                        </a>
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
        },
    }
</script>
