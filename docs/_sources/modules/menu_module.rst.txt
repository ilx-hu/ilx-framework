MenuModule
**************

A Menu modul feladata, hogy egy könnyen használható menü menedzsment interfészt biztosítson.

A modul működése dinamikus, ami annyit jelent, hogy a  menü struktúra egy alapváltozata van a modules.json-be, ami a
telepítés során bekerül az adatbázisba. Ettől kezdve az szerkeszthetővé válik, támogatja a gyakori változtatásokat.

A menü struktúrát egy fában tárolja, aminek gyökere üres. Az első szinten a fő menüpontok vannak, alatta pedig tetszőleges
szintig az almenü pontok.

.. note::

    A hierarchia szintek a használt dizájntól függően változhatnak.


Függőségek
=================
Itt azoknak a moduloknak a listáját találod, amelyek szükségesek a MenuModule működéséhez:

* TwigModule *(kötelező)*
* DatabaseModule *(kötelező)*


Importálás
========================

A modules.json-höz egyszerűen hozzá kell adni a következő bejegyzést:

.. code-block::

    "Menu": []



Paraméterek
========================

Két paramétert lehet beállítani a Menu modulnak:

* structure: A kezdeti menü struktúra leírója. Minden egyes bejegyzésébe 3 értéket kell megadni: name, title, children.
    * name: Egyedi név ami alapján hivatkozni lehet rá
    * title: A megjeleníteni kívánt menü cím
    * children: Ha vannak almenük, akkor ezen belül kell elhelyezni. **Ha nincsenek almenüpontok akkor is szerepelnie kell!**
* url_prefix: Url prefix a route-okhoz

Példa a paraméterek egy beállítására:
.. code-block::

    "Menu": {
        # Menü struktúra leírója
        "structure": {
            {
                "name": "home",
                "title": "Kezdőlap",
                "children": []
            },
            {
                "name": "contacts",
                "title": "Kapcsolat",
                "children": {
                    {
                        "name": "colleagues",
                        "title": "Munkatársak",
                        "children": []
                    },
                    {
                        "name": "addresses",
                        "title": "Címek",
                        "children": []
                    },
                }
            },
        },
        # Url prefix a route-okhoz
        "url_prefix": "/page",

    }

Ebben az esetben a menüpontokhoz kapcsolódó url-ek:

* Kezdőlap: "/page/home"
* Munkatársak: "/page/colleagues"
* Címek: "/page/addresses"

Használata
========================

modules.json paraméterek beállítása után

A menüt a twig fájlokban az app.menu alatt lehet elérni. Példa egy menü generálásra




