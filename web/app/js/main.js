var app = new Vue({
    el: "#app",
    data: {
        playlists: [],
        directories: [],
        files: [],
        showStep1: false,
        showStep2: false,
        showStep3: false,
        playlistName: '',
        directory: '',
        selection: [],
        downloadLink: '',
        creationInProgress: false,
        _autocompleteInstance: null,
    },
    watch: {
        playlists: function (val) {
            let data = {};
            val.forEach(function (item) {
                data[item.name] = null;
            });
            this._autocompleteInstance.updateData(data);
        },
        playlistName: function (val) {
            if (this.playlistName.length > 0) {
                this.showStep2 = true;
            } else {
                this.showStep2 = false;
                this.showStep3 = false;
                this.directory = '';
                this.files = [];
                this.selection = [];
                this.downloadLink = '';
            }
        },
        directory: function (val) {
            let that = this;
            axios.get('/files/' + val)
                .then(function (response) {
                    that.files = response.data;
                    // todo: добавить название директории
                });
        },
        selection: function (val) {
            this.downloadLink = '';
            if (this.playlistName.length && this.selection.length) {
                this.showStep3 = true;
            } else {
                this.showStep3 = false;
                this.downloadLink = '';
            }
        },
    },
    mounted: function () {
        let that = this;
        this._autocompleteInstance = M.Autocomplete.init(document.querySelector('#playlistName'), {
            onAutocomplete: function (a, b, input) {
                that.playlistName = a;
            },
        });

        axios.get('/playlists')
            .then(function (response) {
                that.playlists = response.data;
                that.showStep1 = true;
            });

        axios.get('/files')
            .then(function (response) {
                that.directories = response.data;
            });
    },
    methods: {
        send: function () {
            var that = this;
            this.creationInProgress = true;
            axios.post('/playlists', {name: this.playlistName})
                .then(function (response) {
                    // todo: добавить вывод ссылки
                    that.downloadLink = response.data;
                    that.creationInProgress = false;
                });
        },
    },
});