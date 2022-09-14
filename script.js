let select = document.querySelector('#catselect')
let hideNew = document.querySelector(".catnew")
select.addEventListener('change', e=>{
    if(select.value == "new"){
        hideNew.style.display = "flex";
    }else{
        hideNew.style.display = "none";
    }
})
