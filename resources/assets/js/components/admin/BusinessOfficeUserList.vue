<template>
    <b-card>
        <b-btn variant="info" @click="createUser()" class="mb-4">Create User</b-btn>
        <table class="table table-bordered">
            <thead>
            <th>Username</th>
            <th>Email</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th></th>
            </thead>
            <tbody>
            <tr v-for="user in users" :key="user.id">
                <td>{{ user.username }}</td>
                <td>{{ user.email }}</td>
                <td>{{ user.firstname }}</td>
                <td>{{ user.lastname }}</td>
                <td>
                    <b-btn @click="editUser(user)"><i class="fa fa-edit"></i></b-btn>
                    <b-btn @click="deleteUser(user)" variant="danger"><i class="fa fa-times"></i></b-btn>
                </td>
            </tr>
            </tbody>
        </table>
        <business-office-user-modal :chain="chain" :businesses="businesses" :selectedItem="selectedItem" v-model="officeUserModal" :items="users"></business-office-user-modal>
    </b-card>
</template>

<script>
    export default {
        props: {
            'chain': Object,
            'businesses': Array,
        },

        data() {
            return {
                'users': [],
                'selectedItem': {},
                'officeUserModal': false,
            }
        },

        mounted() {
            this.loadUsers();
        },

        methods: {

            loadUsers() {
                axios.get('/admin/chains/' + this.chain.id + '/users')
                    .then(response => {
                        this.users = response.data.data;
                    })
            },

            editUser(user) {
                this.selectedItem = user;
                this.officeUserModal = true;
            },

            createUser(user) {
                this.selectedItem = null;
                this.officeUserModal = true;
            },

            deleteUser(user) {

                if( this.users.length > 1 ){

                    if (confirm('Are you sure you wish to delete ' + user.username + '?')) {
                        let form = new Form();
                        form.submit('delete', '/admin/chains/' + this.chain.id + '/users/' + user.id)
                            .then(response => {
                                this.loadUsers();
                            })
                            .catch(() => {})
                    }
                } else alert( 'you must have at least one office user for a business!' );
            }

        },
    }
</script>
