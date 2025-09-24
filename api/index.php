<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0" />
  <title>Nangi Reels</title>
  <meta property="og:site_name" content="Nangi Reels">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Watch Hot Reels">
  <meta name="keywords" content="Hot Reels, Nangi Reels, Adult Reels">
  <meta name="robots" content="index, follow">
  <meta name="language" content="English">
  <meta name="author" content="Nangi Reels LLP">
  <meta property="og:title" content="Nangi Reels">
  <meta property="og:description" content="Watch Hot Reels">
  <meta property="og:image" content="/files/your-logo.png">
  <meta property="og:type" content="website">
  <link rel="icon" href="/files/your-logo.png" type="image/x-icon">
  <link rel="stylesheet" href="/files/style.css">
</head>
<body>
  <div class="logo">
    <img src="/files/your-logo.png" alt="Logo">
  </div>
  <div id="video-container"></div>

  <!-- Top Controls -->
  <div id="top-controls">
    <button id="soundBtn"><img id="soundIcon" src="/files/mute.png" alt="Sound"></button>
    <button id="fitBtn">Fit</button>
    <button id="fullscreenBtn"><img src="/files/fullscreen-logo.png" alt="Fullscreen"></button>
    <button id="downloadBtn"><img src="/files/download.png" alt="Download"></button>
    <button id="shareBtn" style="display: none;"><img src="/files/share.png" alt="Share"></button>
    <button id="modeBtn">Reels</button> <!-- Mode toggle button -->
  </div>

  <script>
    const videoContainer = document.getElementById('video-container');
    const soundBtn = document.getElementById('soundBtn');
    const soundIcon = document.getElementById('soundIcon');
    const fitBtn = document.getElementById('fitBtn');
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    const downloadBtn = document.getElementById('downloadBtn');
    const shareBtn = document.getElementById('shareBtn');
    const modeBtn = document.getElementById('modeBtn');

    let videosData = [];
    let videoOrder = [];
    let current = 0;
    let loadedVideos = []; // Track videos that are currently loaded in the DOM
    let visibleRange = 5; // Number of videos to load before and after the current video
    let currentJson = '/files/videos.json';

    let isMuted = true;
    let isContain = true;

    // Load videos from JSON
    function loadVideos(jsonFile) {
      fetch(jsonFile)
        .then(res => res.json())
        .then(data => {
          videosData = data;
          shuffleVideos();
          videoContainer.innerHTML = '';
          current = 0;
          createVideos();
          showVideo(current);
        });
    }

    loadVideos(currentJson);

    function shuffleVideos() {
      videoOrder = [...Array(videosData.length).keys()];
      for (let i = videoOrder.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [videoOrder[i], videoOrder[j]] = [videoOrder[j], videoOrder[i]];
      }
    }

    function createVideos() {
      // Only create videos in the current range + 5 before and after
      const start = Math.max(0, current - visibleRange);
      const end = Math.min(videosData.length, current + visibleRange + 1);

      for (let i = start; i < end; i++) {
        if (!loadedVideos.includes(i)) {
          const video = videosData[videoOrder[i]];

          const wrapper = document.createElement('div');
          wrapper.classList.add('video-wrapper');
          wrapper.dataset.index = i; // Store the index for easy reference

          const vid = document.createElement('video');
          vid.muted = isMuted;
          vid.loop = false;
          vid.playsInline = true;
          vid.setAttribute('preload', 'none');
          vid.style.objectFit = isContain ? 'contain' : 'cover';

          const info = document.createElement('div');
          info.classList.add('video-info');
          info.innerHTML = `
            <div class="video-title">${video.title}</div>
            <div class="progress-bar-container">
              <div class="progress-bar"></div>
            </div>
          `;

          vid.addEventListener('click', () => showInfo(info));

          info.querySelector('.progress-bar-container').addEventListener('click', e => {
            if (!vid.duration) return;
            const rect = e.currentTarget.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const percentage = clickX / rect.width;
            vid.currentTime = percentage * vid.duration;
          });

          vid.addEventListener('timeupdate', () => {
            if (vid.duration) {
              const progress = (vid.currentTime / vid.duration) * 100;
              info.querySelector('.progress-bar').style.width = progress + '%';
            }
          });

          vid.addEventListener('ended', () => nextVideo());

          wrapper.appendChild(vid);
          wrapper.appendChild(info);
          videoContainer.appendChild(wrapper);

          loadedVideos.push(i); // Add to the loaded videos list
        }
      }
    }

    function removeOutOfViewVideos() {
      // Remove videos outside the visible range
      const start = Math.max(0, current - visibleRange);
      const end = Math.min(videosData.length, current + visibleRange + 1);

      document.querySelectorAll('.video-wrapper').forEach(wrapper => {
        const index = parseInt(wrapper.dataset.index);
        if (index < start || index >= end) {
          const vid = wrapper.querySelector('video');
          vid.pause();
          vid.removeAttribute('src');
          vid.load();
          wrapper.remove(); // Remove the video from DOM
          loadedVideos = loadedVideos.filter(i => i !== index); // Remove from loaded list
        }
      });
    }

    function showInfo(info) {
      info.classList.add('show');
      setTimeout(() => info.classList.remove('show'), 5000);
    }

    function loadVideo(index) {
      const wrappers = document.querySelectorAll('.video-wrapper');
      if (index < 0 || index >= wrappers.length) return;

      const vid = wrappers[index].querySelector('video');
      const data = videosData[videoOrder[index]];

      if (!vid.src) {
        vid.src = data.src;
        vid.load();
      }
    }

    function showVideo(index) {
      // Ensure videos in range are created
      createVideos();

      const wrappers = document.querySelectorAll('.video-wrapper');
      wrappers.forEach((wrapper, i) => {
        const vid = wrapper.querySelector('video');
        wrapper.style.transition = "transform 0.5s cubic-bezier(0.22, 1, 0.36, 1)";
        wrapper.style.transform = `translateY(${(i - index) * 100}%)`;

        if (i === index) {
          loadVideo(i);
          vid.muted = isMuted;
          vid.style.objectFit = isContain ? 'contain' : 'cover';
          vid.play().catch(() => {});
        } else if (i === index + 1) {
          loadVideo(i);
          vid.pause();
        } else {
          vid.pause();
          vid.removeAttribute('src');
          vid.load();
        }
      });

      // Remove videos that are out of view
      removeOutOfViewVideos();
    }

    function nextVideo() {
      current = (current + 1) % videoOrder.length;
      showVideo(current);
    }

    function prevVideo() {
      current = (current - 1 + videoOrder.length) % videoOrder.length;
      showVideo(current);
    }

    // Controls
    soundBtn.addEventListener('click', () => {
      isMuted = !isMuted;
      document.querySelectorAll('.video-wrapper video').forEach(v => v.muted = isMuted);
      soundIcon.src = isMuted ? '/files/mute.png' : '/files/unmute.png';
    });

    fitBtn.addEventListener('click', () => {
      isContain = !isContain;
      document.querySelectorAll('.video-wrapper video').forEach(v => {
        v.style.objectFit = isContain ? 'contain' : 'cover';
      });
      fitBtn.textContent = isContain ? 'Fit' : 'Fill';
    });

    fullscreenBtn.addEventListener('click', () => {
      const vid = document.querySelectorAll('.video-wrapper')[current].querySelector('video');

      if (vid.requestFullscreen) vid.requestFullscreen();
      else if (vid.webkitEnterFullscreen) vid.webkitEnterFullscreen();
      else if (vid.webkitRequestFullscreen) vid.webkitRequestFullscreen();
      else if (vid.msRequestFullscreen) vid.msRequestFullscreen();
    });
  </script>
</body>
</html>
