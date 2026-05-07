// ═══════════════════════════════════════════════════════════
//  SENTENCE PACKS  —  edit or add packs freely here
//
//  Each pack: { label, lang, mode, sentences[] }
//    mode: "sentence"   → shown in Writer mode
//          "accountant" → shown in Accountant mode
// ═══════════════════════════════════════════════════════════

const SENTENCE_PACKS = {

  // ── Writer: English ─────────────────────────────────────
  "en": {
    label:   "English — Practice",
    code:    "en",
    version: 1,
    lang:    "en-US",
    mode:    "sentence",
    sentences: [
      "The quick brown fox jumps over the lazy dog.",
      "Practice makes perfect when learning to type.",
      "A good typist never looks at the keyboard.",
      "Speed and accuracy both improve with daily practice.",
      "Focus on accuracy first and speed will follow naturally.",
      "Touch typing is a valuable skill in the digital age.",
      "Consistency is the key to improving your typing speed.",
      "The fingers glide effortlessly across the keys.",
      "Programming is the art of solving complex problems.",
      "Every day is a new opportunity to learn something new.",
      "Blind typing requires focus and muscle memory.",
      "Regular short sessions beat infrequent long ones.",
    ],
  },

  // ── Writer: English proverbs ────────────────────────────
  "en-prov": {
    label:   "English — Proverbs",
    code:    "en-prov",
    version: 1,
    lang:    "en-US",
    mode:    "sentence",
    sentences: [
      "The early bird catches the worm.",
      "Actions speak louder than words.",
      "Better late than never.",
      "Don't judge a book by its cover.",
      "Every cloud has a silver lining.",
      "Look before you leap.",
      "No pain, no gain.",
      "The pen is mightier than the sword.",
      "A stitch in time saves nine.",
      "All that glitters is not gold.",
      "Don't bite the hand that feeds you.",
      "Two wrongs don't make a right.",
      "The grass is always greener on the other side.",
      "Time waits for no one.",
      "You reap what you sow.",
    ],
  },

  // ── Writer: French ──────────────────────────────────────
  "fr": {
    label:   "Français — Pratique",
    code:    "fr",
    version: 1,
    lang:    "fr-FR",
    mode:    "sentence",
    sentences: [
      "Pratiquer régulièrement améliore la vitesse de frappe.",
      "Les doigts glissent rapidement sur le clavier sans effort.",
      "La précision est plus importante que la vitesse au début.",
      "Un bon typiste ne regarde jamais le clavier.",
      "Maîtriser la dactylographie demande de la persévérance.",
      "Le silence du bureau favorise la concentration.",
      "Chaque erreur est une leçon pour progresser davantage.",
      "Le clavier suisse possède des caractères spéciaux utiles.",
      "Taper vite et bien est une compétence précieuse.",
    ],
  },

  // ── Writer: French expressions ──────────────────────────
  "fr-expr": {
    label:   "Français — Expressions",
    code:    "fr-expr",
    version: 1,
    lang:    "fr-FR",
    mode:    "sentence",
    sentences: [
      "Qui vole un œuf vole un bœuf.",
      "L'habit ne fait pas le moine.",
      "Mieux vaut tard que jamais.",
      "Quand le chat est parti, les souris dansent.",
      "Qui sème le vent récolte la tempête.",
      "Tout ce qui brille n'est pas or.",
      "Pierre qui roule n'amasse pas mousse.",
      "Chat échaudé craint l'eau froide.",
      "Après la pluie, le beau temps.",
      "La nuit, tous les chats sont gris.",
      "Les absents ont toujours tort.",
      "Il ne faut pas mettre tous ses œufs dans le même panier.",
      "Il faut battre le fer pendant qu'il est chaud.",
      "On n'apprend pas à un vieux singe à faire la grimace.",
      "Il ne faut pas vendre la peau de l'ours avant de l'avoir tué.",
    ],
  },

  // ── Accountant: numpad exercises ────────────────────────
  "numpad": {
    label:   "Numpad — Basic",
    code:    "numpad",
    version: 1,
    lang:    "en-US",
    mode:    "accountant",
    sentences: [
      "123 + 456 + 789",
      "1234.56 + 7890.12",
      "10000 - 3456.78",
      "47 * 23",
      "9876 / 4",
      "1.5 + 2.5 + 3.5 + 4.5",
      "100 + 200 + 300 + 400",
      "12345 + 67890",
      "999 * 111",
      "1000 / 8",
      "3.14 * 2",
      "25 * 4 + 100",
      "5000 - 1234.56 - 789.01",
      "98765 / 5",
      "1 + 2 + 3 + 4 + 5 + 6 + 7 + 8 + 9",
      "111 + 222 + 333 + 444",
      "9999 - 1111 - 2222",
      "12.5 * 8",
      "750 / 3 + 50",
      "4567.89 + 1234.56",
    ],
  },

};
