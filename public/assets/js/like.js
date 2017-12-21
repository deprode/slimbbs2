"use strict";

function likePlus1(id)
{
    // count++を反映
    let count = document.getElementById(id + '_like').textContent;
    document.getElementById(id + '_like').textContent = (parseInt(count) + 1);
}

function removeLikeButton(id)
{
    document.getElementById(id).querySelector('input[type=submit]').disabled = true;
    document.getElementById(id).disabled = true;
}

function addLike(id) {
    if (document.getElementById(id).disabled) {
        return;
    }

    fetch(add_like_path, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(document.getElementById(id))
    })
    .then((response) => {
        if (!response.ok) {
            throw Error(response.statusText);
        }
        likePlus1(id);
        removeLikeButton(id);
    })
    .catch((e) => {
        console.error(e);
    });
}