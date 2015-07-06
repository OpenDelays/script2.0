# script2.0
script php


ANALISI e ERRORI SCRIPT OVH

Gli script ovh presentano un buon parsing e hanno raccolto efficientemente i dati,
presentano però i seguenti limiti:

- gli script uk e be non funzionano tra le 22-24 e il primo di ogni mese, poichè la 
retrodatazione è eseguita in maiera aritmetica che da errore in quelle circostanze

- lo script belga non distingue gli arrivi dalle partenze

- lo scrirpt italiano seleziona i treni in base all'ora corrente ma impiegando più di un ora,
crea dei buchi o dei doppioni a seconda se gli script in due ore successive si sovrappongono
o scostano.

- il db italiano ha delle date memorizza future quindi impossibili (es. 11-12-2020) di 
indefinibile provenienza



SCRIPT e PARSING

Gli script Ovh esegueno un buon parsing,
ma non tengono conto della codifica dati:

Beglio (Xml)
Italia    (Json) 

Attraverso l'uso di librerie specifiche si ottengono risultati più sicuri e performanti.
Il parsing nel caso inglese (HTML) rimane l'unica possibilità.

Il parsing negli script ovh inoltre veniva eseguito in maniera ripetuta
su tutta la pagina per ogni linea da analizzare, creando un consumo
di risorse non necessarie.
Negli script attuali le pagine vengono analizzate una singola volta,
i dati vengono memorizzati in strutture temporanee e le righe
vengono così analizzate con un notevole risparmio di risorse.

VELOCITA'  E PERFORMANCE

Grazie a queste migliorie introdotte gli script attuali hanno 
mediamente performance fino a quattro volte superiori ai prececenti.
Il tempo medio degli script precedenti è intorno ad uno ora più,
gli attuali si attestano sui quidici minuti.


STRUTTURA DB

Abbiamo a disposizione 25 database mysql. Ne utilizziamo uno per archiviare i dati raccolti dalla precedente versione.
Gli altri per la nuova. L'obbiettivo e trovare il giusto compremesso tra spazio utilizzato e performance al tempo di query.
Per far ciò dobbiamo tenere i database sotto i 300mega (test in corso).
Le proporzioni della mole dati raccolti dagli script a parità di tempo sono:
1 belgio
2 italia
9 Inghilterra

Dove lo script del Belgio ha creato approssimativamente 300mega in sei mesi.
L'idea attuale è di crerare un db ogni semestre ottenendo una longevità di 12 anni.
Ogni db avra 1 tabella per il belgio semestrale 2 tabelle per l'italia trimestrali,
ed infine 6 tabelle mensili per l'inghilterra.
In questo modo le dimensioni dovrebbere rimanere contenute per le singole tabelle
e i periodi di tempo facilmente identificabili in fase di query.







MAIL CRON

E' possibile impostare una gmail (gestita da Raphael) a cui inviare ogni ora tutte le query necessarie a ricreare i db
