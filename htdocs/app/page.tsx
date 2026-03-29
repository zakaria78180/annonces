"use client"

import { useState } from "react"
import { Download, Monitor, Database, FolderOpen, Terminal, CheckCircle2, ExternalLink, Copy, ChevronRight, Server, Globe, FileCode, Folder } from "lucide-react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"

const steps = [
  {
    number: 1,
    title: "Telecharger le projet",
    description: "Recuperez le code source complet du projet PHP",
    icon: Download,
    content: (
      <div className="space-y-4">
        <p className="text-gray-600">Cliquez sur les trois points <strong>...</strong> en haut a droite de l'ecran v0, puis selectionnez <strong>"Download ZIP"</strong>.</p>
        <div className="bg-gray-900 text-gray-100 rounded-lg p-4 font-mono text-sm">
          <div className="flex items-center gap-2 text-gray-400 mb-2">Structure du ZIP :</div>
          <div className="text-emerald-400">php-annonces/</div>
          <div className="pl-4 text-gray-300">
            <div>├── api/</div>
            <div>├── config/</div>
            <div>├── includes/</div>
            <div>├── index.php</div>
            <div>├── login.php</div>
            <div>├── register.php</div>
            <div>├── annonces.php</div>
            <div>├── post.php</div>
            <div>├── account.php</div>
            <div>├── admin.php</div>
            <div>└── ...</div>
          </div>
        </div>
      </div>
    )
  },
  {
    number: 2,
    title: "Installer XAMPP ou WAMP",
    description: "Configurez votre environnement de developpement local",
    icon: Server,
    content: (
      <div className="space-y-4">
        <p className="text-gray-600">Telechargez et installez un serveur local PHP :</p>
        <div className="grid grid-cols-2 gap-4">
          <a href="https://www.apachefriends.org/fr/download.html" target="_blank" rel="noopener noreferrer" className="flex items-center gap-3 p-4 bg-orange-50 border-2 border-orange-200 rounded-lg hover:border-orange-400 transition-colors">
            <div className="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center text-white font-bold">X</div>
            <div>
              <div className="font-semibold text-gray-900">XAMPP</div>
              <div className="text-sm text-gray-500">Windows, Mac, Linux</div>
            </div>
            <ExternalLink className="h-4 w-4 ml-auto text-gray-400" />
          </a>
          <a href="https://www.wampserver.com/" target="_blank" rel="noopener noreferrer" className="flex items-center gap-3 p-4 bg-pink-50 border-2 border-pink-200 rounded-lg hover:border-pink-400 transition-colors">
            <div className="w-12 h-12 bg-pink-500 rounded-lg flex items-center justify-center text-white font-bold">W</div>
            <div>
              <div className="font-semibold text-gray-900">WAMP</div>
              <div className="text-sm text-gray-500">Windows uniquement</div>
            </div>
            <ExternalLink className="h-4 w-4 ml-auto text-gray-400" />
          </a>
        </div>
        <div className="bg-amber-50 border border-amber-200 rounded-lg p-4">
          <p className="text-amber-800 text-sm"><strong>Note :</strong> Lancez Apache depuis le panneau de controle XAMPP/WAMP apres l'installation.</p>
        </div>
      </div>
    )
  },
  {
    number: 3,
    title: "Placer le projet dans htdocs",
    description: "Copiez le dossier php-annonces dans le repertoire du serveur",
    icon: FolderOpen,
    content: (
      <div className="space-y-4">
        <p className="text-gray-600">Extrayez le ZIP et copiez le dossier <code className="bg-gray-100 px-2 py-1 rounded">php-annonces</code> dans :</p>
        <div className="space-y-3">
          <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
            <Badge variant="outline">XAMPP</Badge>
            <code className="text-sm bg-gray-900 text-emerald-400 px-3 py-1 rounded">C:\xampp\htdocs\php-annonces</code>
          </div>
          <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
            <Badge variant="outline">WAMP</Badge>
            <code className="text-sm bg-gray-900 text-emerald-400 px-3 py-1 rounded">C:\wamp64\www\php-annonces</code>
          </div>
          <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
            <Badge variant="outline">Mac</Badge>
            <code className="text-sm bg-gray-900 text-emerald-400 px-3 py-1 rounded">/Applications/XAMPP/htdocs/php-annonces</code>
          </div>
        </div>
      </div>
    )
  },
  {
    number: 4,
    title: "Acceder au site",
    description: "Ouvrez votre navigateur et accedez au projet",
    icon: Globe,
    content: (
      <div className="space-y-4">
        <p className="text-gray-600">Ouvrez votre navigateur et tapez l'adresse suivante :</p>
        <div className="flex items-center gap-3">
          <code className="flex-1 bg-gray-900 text-emerald-400 px-4 py-3 rounded-lg text-lg font-mono">http://localhost/php-annonces/</code>
          <Button variant="outline" size="sm" onClick={() => navigator.clipboard.writeText("http://localhost/php-annonces/")}>
            <Copy className="h-4 w-4" />
          </Button>
        </div>
        <div className="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
          <p className="text-emerald-800 text-sm flex items-center gap-2">
            <CheckCircle2 className="h-5 w-5" />
            La base de donnees SQLite sera creee automatiquement au premier acces !
          </p>
        </div>
      </div>
    )
  },
  {
    number: 5,
    title: "Comptes de test",
    description: "Utilisez ces identifiants pour tester le site",
    icon: Terminal,
    content: (
      <div className="space-y-4">
        <p className="text-gray-600">Deux comptes sont crees automatiquement pour tester :</p>
        <div className="grid grid-cols-2 gap-4">
          <div className="bg-blue-50 border-2 border-blue-200 rounded-lg p-4">
            <Badge className="bg-blue-600 mb-2">Administrateur</Badge>
            <div className="space-y-1 font-mono text-sm">
              <div><span className="text-gray-500">Email:</span> admin@annonces.com</div>
              <div><span className="text-gray-500">Mot de passe:</span> admin123</div>
            </div>
          </div>
          <div className="bg-gray-50 border-2 border-gray-200 rounded-lg p-4">
            <Badge variant="secondary" className="mb-2">Utilisateur</Badge>
            <div className="space-y-1 font-mono text-sm">
              <div><span className="text-gray-500">Email:</span> user@test.com</div>
              <div><span className="text-gray-500">Mot de passe:</span> user123</div>
            </div>
          </div>
        </div>
      </div>
    )
  },
  {
    number: 6,
    title: "Voir la base de donnees avec DB Browser",
    description: "Explorez et modifiez les donnees SQLite",
    icon: Database,
    content: (
      <div className="space-y-4">
        <p className="text-gray-600">Telechargez <strong>DB Browser for SQLite</strong> pour visualiser et modifier la base de donnees :</p>
        <a href="https://sqlitebrowser.org/dl/" target="_blank" rel="noopener noreferrer" className="flex items-center gap-3 p-4 bg-sky-50 border-2 border-sky-200 rounded-lg hover:border-sky-400 transition-colors">
          <div className="w-12 h-12 bg-sky-600 rounded-lg flex items-center justify-center">
            <Database className="h-6 w-6 text-white" />
          </div>
          <div>
            <div className="font-semibold text-gray-900">DB Browser for SQLite</div>
            <div className="text-sm text-gray-500">Gratuit - Windows, Mac, Linux</div>
          </div>
          <ExternalLink className="h-4 w-4 ml-auto text-gray-400" />
        </a>
        
        <div className="bg-gray-900 text-gray-100 rounded-lg p-4 font-mono text-sm">
          <div className="text-gray-400 mb-2">Chemin du fichier de base de donnees :</div>
          <div className="text-emerald-400">php-annonces/database/annonces.db</div>
        </div>
        
        <div className="space-y-2">
          <p className="font-medium text-gray-700">Pour ouvrir la base :</p>
          <ol className="list-decimal list-inside space-y-1 text-gray-600 text-sm">
            <li>Ouvrez DB Browser for SQLite</li>
            <li>Cliquez sur "Ouvrir une base de donnees"</li>
            <li>Naviguez vers <code className="bg-gray-100 px-1 rounded">php-annonces/database/annonces.db</code></li>
            <li>Explorez les tables : utilisateurs, annonces, categories, historique</li>
          </ol>
        </div>
      </div>
    )
  }
]

const projectStructure = [
  { name: "php-annonces/", type: "folder", indent: 0 },
  { name: "api/", type: "folder", indent: 1 },
  { name: "admin/", type: "folder", indent: 2 },
  { name: "moderation.php", type: "file", indent: 3, desc: "Activer/desactiver annonces et utilisateurs" },
  { name: "stats.php", type: "file", indent: 3, desc: "Statistiques du site" },
  { name: "annonces.php", type: "file", indent: 2, desc: "API REST CRUD annonces" },
  { name: "auth.php", type: "file", indent: 2, desc: "API authentification" },
  { name: "categories.php", type: "file", indent: 2, desc: "API REST categories" },
  { name: "utilisateurs.php", type: "file", indent: 2, desc: "Gestion des utilisateurs" },
  { name: "config/", type: "folder", indent: 1 },
  { name: "database.php", type: "file", indent: 2, desc: "Connexion SQLite (PDO)" },
  { name: "helpers.php", type: "file", indent: 2, desc: "Fonctions utilitaires" },
  { name: "init_db.php", type: "file", indent: 2, desc: "Creation des tables" },
  { name: "includes/", type: "folder", indent: 1 },
  { name: "header.php", type: "file", indent: 2, desc: "En-tete HTML + navigation" },
  { name: "footer.php", type: "file", indent: 2, desc: "Pied de page" },
  { name: "database/", type: "folder", indent: 1, desc: "Base SQLite (creee auto)" },
  { name: "uploads/", type: "folder", indent: 1, desc: "Images des annonces" },
  { name: "index.php", type: "file", indent: 1, desc: "Page d'accueil" },
  { name: "login.php", type: "file", indent: 1, desc: "Formulaire de connexion" },
  { name: "register.php", type: "file", indent: 1, desc: "Formulaire d'inscription" },
  { name: "annonces.php", type: "file", indent: 1, desc: "Liste des annonces + recherche" },
  { name: "annonce.php", type: "file", indent: 1, desc: "Detail d'une annonce" },
  { name: "post.php", type: "file", indent: 1, desc: "Publier une annonce" },
  { name: "account.php", type: "file", indent: 1, desc: "Mon compte" },
  { name: "admin.php", type: "file", indent: 1, desc: "Tableau de bord admin" },
  { name: "logout.php", type: "file", indent: 1, desc: "Deconnexion" },
  { name: "api.php", type: "file", indent: 1, desc: "Documentation API" },
]

const dbTables = [
  {
    name: "utilisateurs",
    columns: [
      { name: "id", type: "INTEGER", pk: true },
      { name: "nom", type: "TEXT" },
      { name: "email", type: "TEXT", unique: true },
      { name: "mot_de_passe", type: "TEXT", note: "hash bcrypt" },
      { name: "role", type: "TEXT", note: "user/admin" },
      { name: "actif", type: "INTEGER", note: "0/1" },
      { name: "date_creation", type: "TEXT" },
    ]
  },
  {
    name: "annonces",
    columns: [
      { name: "id", type: "INTEGER", pk: true },
      { name: "titre", type: "TEXT" },
      { name: "description", type: "TEXT" },
      { name: "prix", type: "REAL" },
      { name: "categorie_id", type: "INTEGER", fk: "categories" },
      { name: "utilisateur_id", type: "INTEGER", fk: "utilisateurs" },
      { name: "image", type: "TEXT" },
      { name: "active", type: "INTEGER", note: "0/1" },
      { name: "date_creation", type: "TEXT" },
    ]
  },
  {
    name: "categories",
    columns: [
      { name: "id", type: "INTEGER", pk: true },
      { name: "nom", type: "TEXT", unique: true },
      { name: "description", type: "TEXT" },
    ]
  },
  {
    name: "historique",
    columns: [
      { name: "id", type: "INTEGER", pk: true },
      { name: "type_action", type: "TEXT" },
      { name: "utilisateur_id", type: "INTEGER", fk: "utilisateurs" },
      { name: "annonce_id", type: "INTEGER", fk: "annonces" },
      { name: "details", type: "TEXT" },
      { name: "date_action", type: "TEXT" },
    ]
  }
]

export default function TutorialPage() {
  const [activeStep, setActiveStep] = useState(0)
  const [activeTab, setActiveTab] = useState<"tutorial" | "structure" | "database" | "postman">("tutorial")

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 to-sky-50">
      {/* Header */}
      <header className="bg-gradient-to-r from-[#1e3a5f] to-[#2d5a87] text-white">
        <div className="max-w-6xl mx-auto px-6 py-8">
          <div className="flex items-center gap-4 mb-4">
            <div className="w-14 h-14 bg-amber-500 rounded-xl flex items-center justify-center">
              <FileCode className="h-7 w-7 text-[#1e3a5f]" />
            </div>
            <div>
              <h1 className="text-3xl font-bold">Petites Annonces PHP</h1>
              <p className="text-white/70">Guide d'installation et documentation</p>
            </div>
          </div>
          
          {/* Tabs */}
          <div className="flex gap-2 mt-6">
            <button
              onClick={() => setActiveTab("tutorial")}
              className={`px-5 py-2.5 rounded-lg font-medium transition-colors ${activeTab === "tutorial" ? "bg-white text-[#1e3a5f]" : "bg-white/10 hover:bg-white/20"}`}
            >
              <Monitor className="h-4 w-4 inline mr-2" />
              Tutoriel d'installation
            </button>
            <button
              onClick={() => setActiveTab("structure")}
              className={`px-5 py-2.5 rounded-lg font-medium transition-colors ${activeTab === "structure" ? "bg-white text-[#1e3a5f]" : "bg-white/10 hover:bg-white/20"}`}
            >
              <Folder className="h-4 w-4 inline mr-2" />
              Structure du projet
            </button>
            <button
              onClick={() => setActiveTab("database")}
              className={`px-5 py-2.5 rounded-lg font-medium transition-colors ${activeTab === "database" ? "bg-white text-[#1e3a5f]" : "bg-white/10 hover:bg-white/20"}`}
            >
              <Database className="h-4 w-4 inline mr-2" />
              Schema de la base
            </button>
            <button
              onClick={() => setActiveTab("postman")}
              className={`px-5 py-2.5 rounded-lg font-medium transition-colors ${activeTab === "postman" ? "bg-white text-[#1e3a5f]" : "bg-white/10 hover:bg-white/20"}`}
            >
              <svg className="h-4 w-4 inline mr-2" viewBox="0 0 256 256" fill="currentColor">
                <path d="M128 48C84.65 48 49.6 83.05 49.6 126.4C49.6 169.75 84.65 204.8 128 204.8C171.35 204.8 206.4 169.75 206.4 126.4C206.4 83.05 171.35 48 128 48ZM128 185.6C95.2 185.6 68.8 159.2 68.8 126.4C68.8 93.6 95.2 67.2 128 67.2C160.8 67.2 187.2 93.6 187.2 126.4C187.2 159.2 160.8 185.6 128 185.6Z"/>
              </svg>
              Tester avec Postman
            </button>
          </div>
        </div>
      </header>

      <main className="max-w-6xl mx-auto px-6 py-10">
        {activeTab === "tutorial" && (
          <div className="grid grid-cols-3 gap-8">
            {/* Steps navigation */}
            <div className="col-span-1">
              <div className="sticky top-6 space-y-2">
                {steps.map((step, index) => (
                  <button
                    key={step.number}
                    onClick={() => setActiveStep(index)}
                    className={`w-full flex items-center gap-3 p-3 rounded-lg text-left transition-all ${
                      activeStep === index 
                        ? "bg-[#1e3a5f] text-white shadow-lg" 
                        : "bg-white hover:bg-gray-50 text-gray-700 shadow"
                    }`}
                  >
                    <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold ${
                      activeStep === index ? "bg-amber-500 text-[#1e3a5f]" : "bg-gray-200 text-gray-600"
                    }`}>
                      {step.number}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="font-medium truncate">{step.title}</div>
                    </div>
                    <ChevronRight className={`h-4 w-4 ${activeStep === index ? "text-white/70" : "text-gray-400"}`} />
                  </button>
                ))}
              </div>
            </div>

            {/* Step content */}
            <div className="col-span-2">
              <Card className="shadow-xl border-0">
                <CardHeader className="border-b bg-gray-50/50">
                  <div className="flex items-center gap-4">
                    <div className="w-12 h-12 bg-[#1e3a5f] rounded-xl flex items-center justify-center">
                      {(() => {
                        const Icon = steps[activeStep].icon
                        return <Icon className="h-6 w-6 text-amber-500" />
                      })()}
                    </div>
                    <div>
                      <CardTitle className="text-xl text-[#1e3a5f]">
                        Etape {steps[activeStep].number} : {steps[activeStep].title}
                      </CardTitle>
                      <CardDescription>{steps[activeStep].description}</CardDescription>
                    </div>
                  </div>
                </CardHeader>
                <CardContent className="p-6">
                  {steps[activeStep].content}
                </CardContent>
              </Card>

              {/* Navigation buttons */}
              <div className="flex justify-between mt-6">
                <Button 
                  variant="outline" 
                  onClick={() => setActiveStep(Math.max(0, activeStep - 1))}
                  disabled={activeStep === 0}
                  className="bg-transparent"
                >
                  Etape precedente
                </Button>
                <Button 
                  onClick={() => setActiveStep(Math.min(steps.length - 1, activeStep + 1))}
                  disabled={activeStep === steps.length - 1}
                  className="bg-[#1e3a5f] hover:bg-[#2d5a87]"
                >
                  Etape suivante
                </Button>
              </div>
            </div>
          </div>
        )}

        {activeTab === "structure" && (
          <Card className="shadow-xl border-0">
            <CardHeader>
              <CardTitle className="text-xl text-[#1e3a5f]">Structure du projet PHP</CardTitle>
              <CardDescription>Organisation des fichiers et dossiers</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="bg-gray-900 rounded-lg p-6 font-mono text-sm overflow-auto max-h-[600px]">
                {projectStructure.map((item, index) => (
                  <div 
                    key={index}
                    className="flex items-center gap-2 py-1"
                    style={{ paddingLeft: `${item.indent * 20}px` }}
                  >
                    {item.type === "folder" ? (
                      <Folder className="h-4 w-4 text-amber-400" />
                    ) : (
                      <FileCode className="h-4 w-4 text-sky-400" />
                    )}
                    <span className={item.type === "folder" ? "text-amber-400 font-semibold" : "text-gray-300"}>
                      {item.name}
                    </span>
                    {item.desc && (
                      <span className="text-gray-500 text-xs ml-2">// {item.desc}</span>
                    )}
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        )}

        {activeTab === "database" && (
          <div className="space-y-6">
            <Card className="shadow-xl border-0">
              <CardHeader>
                <CardTitle className="text-xl text-[#1e3a5f]">Schema de la base de donnees SQLite</CardTitle>
                <CardDescription>4 tables pour gerer les utilisateurs, annonces, categories et l'historique</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-2 gap-6">
                  {dbTables.map((table) => (
                    <div key={table.name} className="bg-gray-50 rounded-lg overflow-hidden border">
                      <div className="bg-[#1e3a5f] text-white px-4 py-3 font-semibold flex items-center gap-2">
                        <Database className="h-4 w-4" />
                        {table.name}
                      </div>
                      <div className="divide-y">
                        {table.columns.map((col) => (
                          <div key={col.name} className="px-4 py-2 flex items-center gap-3 text-sm">
                            <code className="font-medium text-gray-900 w-32">{col.name}</code>
                            <Badge variant="secondary" className="text-xs">{col.type}</Badge>
                            {col.pk && <Badge className="bg-amber-500 text-xs">PK</Badge>}
                            {col.unique && <Badge variant="outline" className="text-xs">UNIQUE</Badge>}
                            {col.fk && <Badge variant="outline" className="text-xs border-sky-300 text-sky-700">FK: {col.fk}</Badge>}
                            {col.note && <span className="text-gray-400 text-xs">{col.note}</span>}
                          </div>
                        ))}
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>

            <Card className="shadow-xl border-0">
              <CardHeader>
                <CardTitle className="text-lg text-[#1e3a5f]">Ouvrir avec DB Browser for SQLite</CardTitle>
              </CardHeader>
              <CardContent>
                <ol className="list-decimal list-inside space-y-2 text-gray-600">
                  <li>Telechargez DB Browser for SQLite depuis <a href="https://sqlitebrowser.org/dl/" target="_blank" rel="noopener noreferrer" className="text-sky-600 underline">sqlitebrowser.org</a></li>
                  <li>Ouvrez l'application et cliquez sur <strong>"Ouvrir une base de donnees"</strong></li>
                  <li>Naviguez vers votre dossier <code className="bg-gray-100 px-2 py-0.5 rounded">php-annonces/database/annonces.db</code></li>
                  <li>Explorez les tables dans l'onglet <strong>"Parcourir les donnees"</strong></li>
                  <li>Executez des requetes SQL dans l'onglet <strong>"Executer le SQL"</strong></li>
                </ol>
              </CardContent>
            </Card>
          </div>
        )}

        {activeTab === "postman" && (
          <div className="space-y-6">
            <Card className="shadow-xl border-0 border-l-4 border-l-orange-500">
              <CardHeader>
                <div className="flex items-center gap-4">
                  <div className="w-14 h-14 bg-orange-500 rounded-xl flex items-center justify-center">
                    <svg className="h-8 w-8 text-white" viewBox="0 0 256 256" fill="currentColor">
                      <path d="M128 48C84.65 48 49.6 83.05 49.6 126.4C49.6 169.75 84.65 204.8 128 204.8C171.35 204.8 206.4 169.75 206.4 126.4C206.4 83.05 171.35 48 128 48ZM128 185.6C95.2 185.6 68.8 159.2 68.8 126.4C68.8 93.6 95.2 67.2 128 67.2C160.8 67.2 187.2 93.6 187.2 126.4C187.2 159.2 160.8 185.6 128 185.6Z"/>
                      <circle cx="128" cy="126.4" r="40" />
                    </svg>
                  </div>
                  <div>
                    <CardTitle className="text-xl text-[#1e3a5f]">Tester l'API avec Postman</CardTitle>
                    <CardDescription>Collection complete avec 33 requetes et tests automatises</CardDescription>
                  </div>
                </div>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="bg-orange-50 border border-orange-200 rounded-lg p-5">
                  <h4 className="font-semibold text-orange-900 mb-3">Instructions d'import :</h4>
                  <ol className="list-decimal list-inside space-y-2 text-orange-800">
                    <li>Telechargez le ZIP du projet via <strong>... &gt; Download ZIP</strong></li>
                    <li>Ouvrez <strong>Postman</strong> et cliquez sur <strong>Import</strong> (en haut a gauche)</li>
                    <li>Selectionnez le fichier <code className="bg-white px-2 py-0.5 rounded text-sm">php-annonces/postman/Petites_Annonces_API.postman_collection.json</code></li>
                    <li>La collection apparait dans la barre laterale gauche</li>
                    <li>Executez d'abord <strong>"1.2 Connexion (Admin)"</strong> pour obtenir une session</li>
                  </ol>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="bg-white border-2 border-gray-200 rounded-lg p-4">
                    <div className="flex items-center gap-2 mb-3">
                      <Badge className="bg-green-600">POST</Badge>
                      <span className="font-medium">1. Authentification</span>
                    </div>
                    <ul className="text-sm text-gray-600 space-y-1">
                      <li>Inscription</li>
                      <li>Connexion (Admin / User)</li>
                      <li>Verifier connexion</li>
                      <li>Profil (GET / PUT)</li>
                      <li>Deconnexion</li>
                    </ul>
                  </div>

                  <div className="bg-white border-2 border-gray-200 rounded-lg p-4">
                    <div className="flex items-center gap-2 mb-3">
                      <Badge className="bg-blue-600">GET</Badge>
                      <Badge className="bg-green-600">POST</Badge>
                      <Badge className="bg-amber-600">PUT</Badge>
                      <Badge className="bg-red-600">DELETE</Badge>
                      <span className="font-medium">2. Annonces</span>
                    </div>
                    <ul className="text-sm text-gray-600 space-y-1">
                      <li>Liste avec pagination</li>
                      <li>Filtres (categorie, prix, recherche)</li>
                      <li>Detail d'une annonce</li>
                      <li>Creer / Modifier / Supprimer</li>
                    </ul>
                  </div>

                  <div className="bg-white border-2 border-gray-200 rounded-lg p-4">
                    <div className="flex items-center gap-2 mb-3">
                      <Badge className="bg-purple-600">Admin</Badge>
                      <span className="font-medium">3. Categories</span>
                    </div>
                    <ul className="text-sm text-gray-600 space-y-1">
                      <li>Liste des categories</li>
                      <li>Detail avec annonces</li>
                      <li>Creer / Modifier / Supprimer</li>
                    </ul>
                  </div>

                  <div className="bg-white border-2 border-gray-200 rounded-lg p-4">
                    <div className="flex items-center gap-2 mb-3">
                      <Badge className="bg-purple-600">Admin</Badge>
                      <span className="font-medium">4. Administration</span>
                    </div>
                    <ul className="text-sm text-gray-600 space-y-1">
                      <li>Statistiques generales</li>
                      <li>Stats par categorie / utilisateurs</li>
                      <li>Historique des actions</li>
                      <li>Moderation annonces / utilisateurs</li>
                    </ul>
                  </div>
                </div>

                <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                  <h4 className="font-semibold text-red-900 mb-2">5. Tests de validation</h4>
                  <p className="text-red-800 text-sm">La collection inclut des tests d'erreurs pour verifier que l'API gere correctement :</p>
                  <div className="flex flex-wrap gap-2 mt-2">
                    <Badge variant="outline" className="border-red-300">Email invalide (400)</Badge>
                    <Badge variant="outline" className="border-red-300">Mot de passe court (400)</Badge>
                    <Badge variant="outline" className="border-red-300">Non authentifie (401)</Badge>
                    <Badge variant="outline" className="border-red-300">Non autorise (403)</Badge>
                    <Badge variant="outline" className="border-red-300">Ressource inexistante (404)</Badge>
                  </div>
                </div>

                <div className="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                  <h4 className="font-semibold text-emerald-900 mb-2">Tests automatises inclus</h4>
                  <p className="text-emerald-800 text-sm">Chaque requete contient des scripts de test Postman pour valider automatiquement les reponses (status code, structure JSON, valeurs attendues).</p>
                </div>
              </CardContent>
            </Card>

            <Card className="shadow-xl border-0">
              <CardHeader>
                <CardTitle className="text-lg text-[#1e3a5f]">Exemple de requete cURL</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="bg-gray-900 rounded-lg p-4 font-mono text-sm overflow-auto">
                  <div className="text-gray-400 mb-2"># Connexion admin</div>
                  <div className="text-emerald-400">curl -X POST http://localhost/php-annonces/api/auth.php?action=connexion \</div>
                  <div className="text-emerald-400 pl-4">-H "Content-Type: application/json" \</div>
                  <div className="text-emerald-400 pl-4">{'-d \'{"email":"admin@annonces.com","mot_de_passe":"admin123"}\''}</div>
                  
                  <div className="text-gray-400 mt-4 mb-2"># Liste des annonces</div>
                  <div className="text-emerald-400">curl http://localhost/php-annonces/api/annonces.php</div>
                  
                  <div className="text-gray-400 mt-4 mb-2"># Creer une annonce (avec cookie de session)</div>
                  <div className="text-emerald-400">curl -X POST http://localhost/php-annonces/api/annonces.php \</div>
                  <div className="text-emerald-400 pl-4">-H "Content-Type: application/json" \</div>
                  <div className="text-emerald-400 pl-4">-b "PHPSESSID=votre_session_id" \</div>
                  <div className="text-emerald-400 pl-4">{'-d \'{"titre":"Test","description":"Description test","prix":99}\''}</div>
                </div>
              </CardContent>
            </Card>
          </div>
        )}
      </main>

      {/* Footer */}
      <footer className="bg-[#1e3a5f] text-white py-8 mt-16">
        <div className="max-w-6xl mx-auto px-6 text-center">
          <p className="text-white/60 text-sm">Projet PHP/SQLite - Petites Annonces | Compatible DB Browser for SQLite</p>
        </div>
      </footer>
    </div>
  )
}
