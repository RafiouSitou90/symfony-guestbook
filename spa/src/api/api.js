const fetchCollection = (path) => {
    return fetch('http://127.0.0.1:8000' + path)
        .then(response => response.json())
        .then(json => json["hydra:member"])
    ;
}

export const findConferences = () => {
    return fetchCollection('/api/conferences');
}

export const findComments = (conference) => {
    return fetchCollection('/api/comments?conference='+conference.id);
}
