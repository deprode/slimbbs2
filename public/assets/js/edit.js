"use strict";

function toggleEditBox(element) {
    element.classList.toggle('hidden');
}

function showEditBox(id) {
    toggleEditBox(document.getElementById(id + '-to').parentElement);
    toggleEditBox(document.getElementById(id));
}

function hideEditBox(id) {
    toggleEditBox(document.getElementById(id + '-to').parentElement);
    toggleEditBox(document.getElementById(id));
}

function updateComment(comment_id) {
    const form = document.getElementById('edit-' + comment_id + '-to');
    toggleEditBox(form.parentElement);
    toggleEditBox(document.getElementById('edit-' + comment_id));
    const pre_comment = document.getElementById(comment_id).textContent;
    document.getElementById(comment_id).textContent = form.elements.comment.value;

    fetch(update_path, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form)
    })
    .then((response) => {
        if (!response.ok) {
            throw Error(response.statusText);
        }
    })
    .catch((e) => {
        console.error(e);
        document.getElementById(comment_id).textContent = pre_comment;
        toggleEditBox(document.getElementById(comment_id + '-to').parentElement);
        toggleEditBox(document.getElementById(comment_id));
    });
}