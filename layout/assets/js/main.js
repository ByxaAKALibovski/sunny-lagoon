function switchImageHouse(item_image){
    let newSrc = item_image.getAttribute('src');
    let activeImageBlock = item_image.parentNode.parentNode.querySelector('.active__image');
    let imageList = item_image.parentNode;

    imageList.querySelector('img.active').classList.remove('active');
    activeImageBlock.setAttribute("src", newSrc);
    item_image.classList.add('active');
}

document.addEventListener('click', function(e){
    if(e.target.parentNode.classList.contains('list__image')) switchImageHouse(e.target);

    if(e.target.hasAttribute('data-popup') && e.target.classList.contains('btn')) document.querySelector(`.popup[data-popup="${e.target.getAttribute('data-popup')}"]`).classList.add('active');
    if(e.target.classList.contains('over') && e.target.parentNode.classList.contains('popup')) e.target.parentNode.classList.remove('active');
})