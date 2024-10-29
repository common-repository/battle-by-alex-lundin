const {asl_battles_rest_uri, nonce} = asl_battles_public

async function getBattles() {
    let response = await fetch( `${asl_battles_rest_uri}asl-battle/v1/battles`, {
        method: 'GET',
        credentials: 'same-origin',
    } );

    if (response.ok) {
        let json = await response.json();
        json.map( item => {
            for (let i = 0; i < localStorage.length; i++) {
                let key = localStorage.key( i );
                if (key === `${item.id}`) {
                    const el = document.querySelector( `div[data-battle = "${key}"] .upvote` )
                    el.classList.add( 'upvote-disabled' )
                    el.removeChild( el.querySelector( 'span' ) )
                }
            }
            item.arguments.map( argument => {
                for (let i = 0; i < localStorage.length; i++) {
                    let key = localStorage.key( i );
                    if (key === `${argument.id_item}-${argument.id}`) {
                        const el = document.querySelector( `div[data-argument = "${argument.id}"] .upvote` )
                        el.classList.add( 'upvote-disabled' )
                        el.removeChild( el.querySelector( 'span' ) )
                    }
                }
            } )

            item.comments.map( comment => {
                for (let i = 0; i < localStorage.length; i++) {
                    let key = localStorage.key( i );
                    if (key === `${comment.id}-${comment.comment_argument_id}`) {
                        const el = document.querySelector( `span[data-comment = "${comment.id}"]` )
                        el.classList.add( 'upvote-disabled' )
                        el.removeChild( el.querySelector( 'span' ) )
                    }
                }
            } )
        } )
        return json
    }
}

getBattles().then( result => result )


async function updRatingArgument( el ) {
    const id = el.getAttribute( 'data-battle' )
    const arg_id = el.getAttribute( 'data-argument' )
    const rating = el.getAttribute( 'data-rating' )
    const body = new FormData()
    body.append( 'id', arg_id )
    body.append( 'id_item', id )
    body.append( 'rating', parseInt( rating ) + 1 )
    body.append( '_wpnonce', nonce )
    await fetch( `${asl_battles_rest_uri}asl-battle/v1/battles/${id}/arguments/${arg_id}`, {
        method: 'POST',
        credentials: 'same-origin',
        body: body
    } );
    localStorage.setItem( `${id}-${arg_id}`, true )
    el.textContent = parseInt( rating ) + 1
    el.classList.add( 'upvote-disabled' )
}

async function updRatingComment( el ) {
    const id = el.getAttribute( 'data-battle' )
    const comment_id = el.getAttribute( 'data-comment' )
    const comment_rating = el.getAttribute( 'data-rating' )
    const arg_id = el.getAttribute( 'data-argument' )
    const body = new FormData()
    body.append( 'comment_rating', parseInt( comment_rating ) + 1 )
    body.append( '_wpnonce', nonce )
    await fetch( `${asl_battles_rest_uri}asl-battle/v1/battles/${id}/comments/${comment_id}`, {
        method: 'POST',
        credentials: 'same-origin',
        body: body
    } );
    localStorage.setItem( `${comment_id}-${arg_id}`, true )
    el.textContent = parseInt( comment_rating ) + 1
    el.classList.add( 'upvote-disabled' )
}

async function updRatingBattle( el ) {
    const id = el.getAttribute( 'data-battle' )
    const rating = el.getAttribute( 'data-rating' )
    const body = new FormData()
    body.append( 'id', id )
    body.append( 'rating', parseInt( rating ) + 1 )
    body.append( '_wpnonce', nonce )

    await fetch( `${asl_battles_rest_uri}asl-battle/v1/battles/${id}`, {
        method: 'POST',
        credentials: 'same-origin',
        body: body
    } );
    localStorage.setItem( `${id}`, true )
    el.textContent = parseInt( rating ) + 1
    el.classList.add( 'upvote-disabled' )
}

function openCommentForm( el ) {
    const id = el.getAttribute( 'data-argument' )
    const form = document.querySelector( `.block-battle[data-argument = "${id}"]` )
    form.style.display = null;

}

async function formSubmit( form ) {
    if (!form || form.nodeName !== "FORM") {
        return;
    }
    const id = form.getAttribute('data-argument')
    const body = new FormData()
    body.append( 'comment_battle_id', form.querySelector( '[name="comment_battle_id"]' ).value )
    body.append( 'comment_argument_id', form.querySelector( '[name="comment_argument_id"]' ).value )
    body.append( 'comment_author', form.querySelector( '[name="comment_author"]' ).value )
    body.append( 'comment_text', form.querySelector( '[name="comment_text"]' ).value )
    body.append( 'comment_moderate', 0 )
    body.append( 'comment_rating', 0 )
    body.append( '_wpnonce', nonce )


    let response = await fetch( `${asl_battles_rest_uri}asl-battle/v1/battles/${form.querySelector( '[name="comment_battle_id"]' ).value}/comments`, {
        method: 'POST',
        credentials: 'same-origin',
        body: body
    } );

    if (response.ok) {
        form.style.display = 'none'
        document.querySelector(`.window-success[data-argument = "${id}"]`).style.display = null
    }
}

async function formSubmitArg( form ) {
    if (!form || form.nodeName !== "FORM") {
        return;
    }
    const id = form.getAttribute('data-battle')
    const body = new FormData()
    body.append( 'id_item', form.querySelector( '[name="id_item"]' ).value )
    body.append( 'title', form.querySelector( '[name="title"]' ).value )
    body.append( 'argument', form.querySelector( '[name="argument"]' ).value )
    body.append( 'text', form.querySelector( '[name="text"]' ).value )
    body.append( 'username', form.querySelector( '[name="username"]' ).value )
    body.append( 'moderate', 1 )
    body.append( 'rating', 0 )
    body.append( '_wpnonce', nonce )


    let response = await fetch( `${asl_battles_rest_uri}asl-battle/v1/battles/${id}/arguments/`, {
        method: 'POST',
        credentials: 'same-origin',
        body: body
    } );

    if (response.ok) {
        form.style.display = 'none'
        document.querySelector(`.window-success[data-battle = "${id}"]`).style.display = null
    }
}
