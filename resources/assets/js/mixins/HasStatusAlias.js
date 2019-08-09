export default {

    computed : {

        statusAliasOptions() {

            if ( ! this.statusAliases || !this.statusAliases.client ) {
                return [];
            }

            return this.statusAliases.client.filter(item => {
                return item.active == this.active;
            }).map(item => {
                return {
                    value: item.id,
                    text: item.name,
                };
            });
        },
    }
}
