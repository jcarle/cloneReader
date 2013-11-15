
/** Para resetear los feeds que alguna vez respondieron mal; 
TODO: implementar logica de reintentos antes de marcarlos como feeds muertos
*/
UPDATE feeds SET feedLastUpdate = null, statusId = 0;

/* Para que vuelva a buscar los logos */
UPDATE feeds SET feedIcon = null;


