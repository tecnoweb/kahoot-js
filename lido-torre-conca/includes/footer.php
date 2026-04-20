<!-- FOOTER -->
<footer class="bg-ocean-dark text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">

            <!-- Brand -->
            <div class="lg:col-span-2">
                <div class="flex items-center space-x-3 mb-5">
                    <div class="flex items-center justify-center w-14 h-14 bg-sand rounded-full shadow-lg">
                        <svg viewBox="0 0 48 48" class="w-9 h-9" fill="none">
                            <circle cx="24" cy="24" r="22" fill="#0c2340"/>
                            <path d="M8 32 Q24 12 40 32" stroke="#d4a847" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                            <path d="M6 36 Q24 18 42 36" stroke="#38bdf8" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                            <circle cx="24" cy="17" r="5" fill="#d4a847"/>
                            <line x1="24" y1="17" x2="24" y2="32" stroke="#d4a847" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-display font-bold text-xl">Lido Torre Conca</div>
                        <div class="text-sand text-xs tracking-widest uppercase">Stabilimento Balneare</div>
                    </div>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed max-w-xs">
                    Il tuo angolo di paradiso sul mare. 60 postazioni con lettini e ombrelloni, servizi di noleggio, food&amp;drinks e tanto sole.
                </p>
                <div class="flex space-x-3 mt-5">
                    <a href="<?= waLink('Ciao! Vorrei informazioni.') ?>" target="_blank" rel="noopener"
                       class="flex items-center space-x-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-full transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        <span>WhatsApp</span>
                    </a>
                </div>
            </div>

            <!-- Orari -->
            <div>
                <h4 class="font-semibold text-sand mb-4 uppercase text-xs tracking-widest">Orari</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li class="flex justify-between"><span>Lun – Dom</span><span class="text-white font-medium">08:30 – 19:00</span></li>
                    <li class="flex justify-between"><span>Mezza giornata</span><span class="text-white font-medium">14:00 – 19:00</span></li>
                </ul>
                <h4 class="font-semibold text-sand mb-4 mt-6 uppercase text-xs tracking-widest">Prenotazioni</h4>
                <ul class="space-y-1 text-sm text-gray-400">
                    <li>✓ Giornata intera</li>
                    <li>✓ Mezza giornata</li>
                    <li>✓ Multi-giorno</li>
                    <li>✓ Abbonamento settimanale</li>
                    <li>✓ Abbonamento mensile</li>
                </ul>
            </div>

            <!-- Contatti -->
            <div>
                <h4 class="font-semibold text-sand mb-4 uppercase text-xs tracking-widest">Contatti</h4>
                <ul class="space-y-3 text-sm text-gray-400">
                    <li class="flex items-start space-x-2">
                        <span class="mt-0.5">📍</span>
                        <span>Torre Conca, Palermo<br>Sicilia, Italia</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <span>📧</span>
                        <a href="mailto:info@lidotorreconca.it" class="hover:text-sand transition-colors">info@lidotorreconca.it</a>
                    </li>
                    <li class="flex items-center space-x-2">
                        <span>📱</span>
                        <a href="tel:+393471234567" class="hover:text-sand transition-colors">+39 347 123 4567</a>
                    </li>
                    <li class="flex items-center space-x-2">
                        <span>🌐</span>
                        <a href="https://lidotorreconca.it" class="hover:text-sand transition-colors">lidotorreconca.it</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row items-center justify-between text-xs text-gray-500 space-y-2 md:space-y-0">
            <p>&copy; <?= date('Y') ?> Lido Torre Conca. Tutti i diritti riservati.</p>
            <div class="flex space-x-4">
                <a href="#" class="hover:text-sand transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-sand transition-colors">Cookie Policy</a>
                <a href="/admin/login.php" class="hover:text-sand transition-colors">Area Gestionale</a>
            </div>
        </div>
    </div>
</footer>

<script src="/assets/js/main.js"></script>
</body>
</html>
