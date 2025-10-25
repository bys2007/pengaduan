document.addEventListener('DOMContentLoaded', function () {
    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const closeImageModal = document.getElementById('closeImageModal');

    document.querySelectorAll('.clickable-image').forEach(img => {
      img.addEventListener('click', function () {
        modalImage.src = this.src;
        imageModal.classList.remove('hidden');
      });
    });

    const closeModal = () => {
      imageModal.classList.add('hidden');
      modalImage.src = "";
    };

closeImageModal.addEventListener('click', closeModal);
imageModal.addEventListener('click', function (event) {
    if (event.target === imageModal) {
      closeModal();
    }
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const detailModal = document.getElementById('detailModal');
  const closeModalBtn = document.getElementById('closeModalBtn');
  const detailButtons = document.querySelectorAll('.detail-btn');

  detailButtons.forEach(button => {
    button.addEventListener('click', function () {
      document.getElementById('modal-id').textContent = this.dataset.id;
      document.getElementById('modal-tanggal').textContent = this.dataset.tanggal;
      document.getElementById('modal-nik').textContent = this.dataset.nik;
      document.getElementById('modal-laporan').textContent = this.dataset.laporan;
      // Foto
      if (this.dataset.foto) {
        document.getElementById('modal-foto').innerHTML = `<img src="../foto_pengaduan/${this.dataset.foto}" class="h-24 w-24 object-cover rounded clickable-image cursor-pointer" style="width: auto; height: 200px;"/>`;
      } else {
        document.getElementById('modal-foto').innerHTML = '<span class="text-gray-400 italic">-</span>';
      }
      // Tanggapan
      document.getElementById('modal-tanggapan').textContent = this.dataset.tanggapan ? this.dataset.tanggapan : 'Belum ada tanggapan.';
      document.getElementById('modal-tgl-tanggapan').textContent = this.dataset.tgl_tanggapan ? this.dataset.tgl_tanggapan : '-';
      // Status
      let status = this.dataset.status;
      let statusHtml = '';
      if (status === '0') {
        statusHtml = '<span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-semibold">0</span>';
      } else if (status === 'proses') {
        statusHtml = '<span class="bg-yellow-500 text-white px-2 py-1 rounded text-xs font-semibold">Proses</span>';
      } else if (status === 'selesai') {
        statusHtml = '<span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-semibold">Selesai</span>';
      } else {
        statusHtml = status;
      }
      document.getElementById('modal-status').innerHTML = statusHtml;

      detailModal.classList.remove('hidden');
    });
  });

  closeModalBtn.addEventListener('click', function () {
    detailModal.classList.add('hidden');
  });

  detailModal.addEventListener('click', function (event) {
    if (event.target === detailModal) {
      detailModal.classList.add('hidden');
    }
  });
});

document.addEventListener('DOMContentLoaded', function () {
      // Modal Edit Tanggapan
      const editTanggapanModal = document.getElementById('editTanggapanModal');
      const editTanggapanBtn = document.getElementById('editTanggapanBtn');
      const cancelEditBtn = document.getElementById('cancelEditBtn');

      if (editTanggapanBtn) {
        editTanggapanBtn.addEventListener('click', () => {
          editTanggapanModal.classList.remove('hidden');
        });
      }

      if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', () => {
          editTanggapanModal.classList.add('hidden');
        });
      }

      // Modal Beri Tanggapan
      const beriTanggapanModal = document.getElementById('beriTanggapanModal');
      const beriTanggapanBtn = document.getElementById('beriTanggapanBtn');
      const cancelBeriTanggapanBtn = document.getElementById('cancelBeriTanggapanBtn');

      if (beriTanggapanBtn) {
        beriTanggapanBtn.addEventListener('click', () => {
          beriTanggapanModal.classList.remove('hidden');
        });
      }

      if (cancelBeriTanggapanBtn) {
        cancelBeriTanggapanBtn.addEventListener('click', () => {
          beriTanggapanModal.classList.add('hidden');
        });
      }

      // SHOW PASSWORD
      const showPasswordBtn = document.getElementById('showPasswordBtn');
      const passwordContainer = document.getElementById('passwordContainer');
      const passwordText = document.getElementById('passwordText');

      if (showPasswordBtn) {
        showPasswordBtn.addEventListener('click', () => {
          const isHidden = passwordContainer.classList.contains('hidden');

          if (isHidden) {
            // Jika tersembunyi, tampilkan password
            const password = showPasswordBtn.dataset.password;
            passwordText.textContent = password;
            passwordContainer.classList.remove('hidden');
            showPasswordBtn.textContent = 'Sembunyikan Password';
          } else {
            // Jika terlihat, sembunyikan lagi
            passwordContainer.classList.add('hidden');
            passwordText.textContent = ''; // Kosongkan teks untuk keamanan
            showPasswordBtn.textContent = 'Lihat Password';
          }
        });
      }
    });