<template>
    <div class="quick-search">
        <user-search-dropdown placeholder="Quick Search Active Clients and Caregivers" @selectUser="openProfile" />
    </div>
</template>

<script>
export default {
    name: 'QuickSearch',

    data: () => ({
        options: [],
        filter: '',
        isSearching: false,
        loading: false,
    }),

    methods: {
        openProfile(user) {
            window.location = `/business/${user.role_type}s/${user.id}`;
        },
    }
}

</script>