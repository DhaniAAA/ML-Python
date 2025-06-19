document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('sentimentForm');
    const resultDiv = document.getElementById('result');
    let sentimentChart = null;

    // Tampilkan loader
    function showLoading() {
        resultDiv.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Menganalisis teks...</p>
            </div>
        `;
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Tampilkan loader saat mulai analisis
        showLoading();
        
        const formData = new FormData(form);
        
        fetch('analyze.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Cek respons yang diterima untuk debugging
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Response error:', text);
                    throw new Error(`Server mengembalikan status: ${response.status} - ${response.statusText}`);
                });
            }
            
            return response.text().then(text => {
                // Coba parse respons sebagai JSON
                try {
                    return JSON.parse(text);
                } catch (err) {
                    console.error('JSON parse error:', text);
                    throw new Error('Server mengembalikan respons yang tidak valid (bukan JSON)');
                }
            });
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Periksa jika ada warning dari server
            if (data.warning) {
                let exampleWords = '';
                if (data.sample_words && data.sample_words.length > 0) {
                    exampleWords = `
                        <div class="mt-3">
                            <p class="mb-1">Beberapa contoh kata yang terdapat dalam model:</p>
                            <ul class="list-inline">
                                ${data.sample_words.map(word => `<li class="list-inline-item"><span class="badge bg-info">${word}</span></li>`).join('')}
                            </ul>
                        </div>
                    `;
                }
                
                let processedText = '';
                if (data.processed_text) {
                    processedText = `
                        <div class="mt-3">
                            <p class="mb-1">Hasil preprocessing teks Anda:</p>
                            <div class="alert alert-light">
                                "${data.processed_text || 'Tidak ada kata yang tersisa setelah preprocessing'}"
                            </div>
                            <small class="text-muted">Teks di atas adalah hasil setelah menghapus stopwords dan stemming. Kata-kata inilah yang dicoba dicocokkan dengan model.</small>
                        </div>
                    `;
                }
                
                resultDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <h4 class="alert-heading">Peringatan</h4>
                        <p>${data.message}</p>
                        ${processedText}
                        ${exampleWords}
                        <hr>
                        <p class="mb-0">Coba gunakan kata-kata umum dalam Bahasa Indonesia yang mungkin memiliki sentimen.</p>
                    </div>
                `;
                return; // Keluar dari fungsi jika ada warning
            }
            
            // Tampilkan hasil analisis
            let sentimentText = data.sentiment;
            let alertClass = 'info';
            
            switch(data.sentiment) {
                case 'positive':
                    alertClass = 'success';
                    sentimentText = 'Positif';
                    break;
                case 'negative':
                    alertClass = 'danger';
                    sentimentText = 'Negatif';
                    break;
                case 'neutral':
                    alertClass = 'secondary';
                    sentimentText = 'Netral';
                    break;
            }
            
            resultDiv.innerHTML = `
                <div class="alert alert-${alertClass}">
                    <h4 class="alert-heading">Hasil Analisis</h4>
                    <p>Teks asli: "${data.text}"</p>
                    <p>Sentimen: ${sentimentText}</p>
                    ${data.method ? `<p class="small text-muted">Metode analisis: ${data.method === 'lexicon' ? 'Lexicon (berbasis kamus)' : 'Machine Learning'}</p>` : ''}
                </div>
            `;
            
            // Update word cloud jika ada data frekuensi kata
            if (data.word_frequencies) {
                updateWordCloud(data.word_frequencies);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h4 class="alert-heading">Error</h4>
                    <p>Terjadi kesalahan: ${error.message}</p>
                    <p class="small text-muted">Silakan periksa konsol browser untuk detail lebih lanjut.</p>
                </div>
            `;
        });
    });

    function updateWordCloud(wordFrequencies) {
        const width = document.getElementById('wordcloud').offsetWidth;
        const height = 300;
        
        // Clear previous word cloud
        document.getElementById('wordcloud').innerHTML = '';
        
        const words = Object.entries(wordFrequencies).map(([text, value]) => ({
            text,
            size: Math.max(20, Math.min(60, value * 10)) // Scale font size between 20 and 60px
        }));
        
        const layout = d3.layout.cloud()
            .size([width, height])
            .words(words)
            .padding(5)
            .rotate(() => 0)
            .fontSize(d => d.size)
            .on('end', draw);
            
        layout.start();
        
        function draw(words) {
            d3.select('#wordcloud')
                .append('svg')
                .attr('width', width)
                .attr('height', height)
                .append('g')
                .attr('transform', `translate(${width/2},${height/2})`)
                .selectAll('text')
                .data(words)
                .enter()
                .append('text')
                .style('font-size', d => `${d.size}px`)
                .style('fill', () => `hsl(${Math.random() * 360}, 70%, 50%)`)
                .attr('text-anchor', 'middle')
                .attr('transform', d => `translate(${d.x},${d.y})rotate(${d.rotate})`)
                .text(d => d.text);
        }
    }
}); 