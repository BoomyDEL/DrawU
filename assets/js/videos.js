const like=document.getElementById('like'), likes=document.getElementById('likes');let liked=false,nLikes=0;
like.addEventListener('click',()=>{liked=!liked;nLikes=Math.max(0,nLikes+(liked?1:-1));like.classList.toggle('liked',liked);like.setAttribute('aria-pressed',String(liked));likes.textContent=nLikes});
const panel=document.getElementById('panelComentarios'), abrir=document.getElementById('abrirComentarios'), input=document.getElementById('textoComentario');
function toggleComentarios(show){panel.classList.toggle('abierto',show);panel.setAttribute('aria-hidden',String(!show));abrir.setAttribute('aria-expanded',String(show));if(show)input.focus()}
abrir.addEventListener('click',()=>toggleComentarios(!panel.classList.contains('abierto')));document.getElementById('cerrarComentarios').addEventListener('click',()=>toggleComentarios(false));
let nComentarios=0;document.getElementById('formComentario').addEventListener('submit',e=>{e.preventDefault();const t=input.value.trim();if(!t)return;document.getElementById('sinComentarios').style.display='none';const d=document.createElement('div');d.className='comentario';const b=document.createElement('strong');b.textContent='Tú';const s=document.createElement('span');s.textContent=t;d.append(b,s);document.getElementById('listaComentarios').append(d);document.getElementById('contadorComentarios').textContent=++nComentarios;input.value=''});
const video=document.getElementById('video'),shell=document.getElementById('videoShell'),sound=document.getElementById('sonido');sound.addEventListener('click',()=>{video.muted=!video.muted;sound.textContent=video.muted?'🔇':'🔊'});
document.getElementById('compartir').addEventListener('click',async()=>{try{if(navigator.share)await navigator.share({title:'Video',url:location.href});else if(navigator.clipboard){await navigator.clipboard.writeText(location.href);alert('Enlace copiado.')}else alert('Copia el enlace desde la barra de direcciones.')}catch(e){if(e.name!=='AbortError')alert('No se pudo compartir el enlace.')}});



// Lista de videos: agrega aquí las rutas cuando haya contenido
const listaVideos=['videos/mi_video.mp4'];
let indiceVideo=0;
const btnAnterior=document.getElementById('videoAnterior'),btnSiguiente=document.getElementById('videoSiguiente');
function mostrarVideo(i){
    if(!listaVideos.length){shell.classList.remove('has-video');btnAnterior.disabled=btnSiguiente.disabled=true;return}
    indiceVideo=Math.max(0,Math.min(i,listaVideos.length-1));
    video.src=listaVideos[indiceVideo];shell.classList.add('has-video');
    btnAnterior.disabled=indiceVideo===0;btnSiguiente.disabled=indiceVideo===listaVideos.length-1;
}
btnAnterior.addEventListener('click',()=>mostrarVideo(indiceVideo-1));
btnSiguiente.addEventListener('click',()=>mostrarVideo(indiceVideo+1));
mostrarVideo(0);

