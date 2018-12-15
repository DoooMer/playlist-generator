(function () {

    function Service() {
        this.addFilesQueue = [];
        this._doAddFilesQueue = function () {
            let task = this.addFilesQueue.shift();

            if (!task) {
                return Promise.resolve();
            }

            let self = this;

            return axios.post('/files', task)
                .then(() => {
                    return self._doAddFilesQueue();
                });
        };
    }
    Service.prototype.loadPlaylists = function () {
        return axios.get('/playlists');
    };
    Service.prototype.loadDirectories = function () {
        return axios.get('/files');
    };
    Service.prototype.loadFiles = function (dir) {
        return axios.get('/files/' + dir);
    };
    Service.prototype.addFiles = function (playlist, dir, files) {
        this.addFilesQueue = [];

        files.forEach((name) => {
            this.addFilesQueue.push({playlist: playlist, directory: dir, basename: name});
        });

        return this._doAddFilesQueue();
    };
    Service.prototype.createPlaylist = function (name) {
        return axios.post('/playlists', {name: name});
    };

    let service = new Service();

    let app = new Vue({
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
                service.loadFiles(val)
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
            this._autocompleteInstance = M.Autocomplete.init(
                document.querySelector('#playlistName'),
                {
                    onAutocomplete: function (a, b, input) {
                        that.playlistName = a;
                    },
                }
            );

            service.loadPlaylists()
                .then((response) => {
                    that.playlists = response.data;
                    that.showStep1 = true;
                });

            service.loadDirectories()
                .then((response) => {
                    that.directories = response.data;
                });
        },
        methods: {
            send: function () {
                let that = this;
                this.creationInProgress = true;

                service.addFiles(this.playlistName, this.directory, this.selection)
                    .then(() => {
                        return service.createPlaylist(that.playlistName)
                    })
                    .then((response) => {
                        that.downloadLink = response.data;
                    })
                    .catch(() => {
                        that.downloadLink = '';
                    })
                    .finally(() => {
                        that.creationInProgress = false;
                    });
            },
        },
    });

})();