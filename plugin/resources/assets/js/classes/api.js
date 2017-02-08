export default class Api {

    get(url, data) {
        return new Promise((resolve, reject) => {
            axios.get(url, { params: data })
                .then(response => resolve(response.data))
                .catch(error => {
                    console.log(error);
                    reject(error);
                });
        });
    }

    post(url, data) {
        return new Promise((resolve, reject) => {
            axios.post(url, data)
                .then(response => resolve(response.data))
                .catch(error => {
                    console.log(error);
                    reject(error);
                });
        });
    }
}
