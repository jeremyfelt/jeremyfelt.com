// A small IndexedDB wrapper with two stores:
//   outbox  queued posts, likes and draft saves, keyed by client id
//   blobs   image data for the composer and the outbox, keyed by a uuid
//
// If IndexedDB cannot be opened, an in-memory stand-in keeps the app
// working for the session; `durable` tells callers which one they got.

const DB_NAME = 'writenow';
const DB_VERSION = 1;
export const STORES = [ 'outbox', 'blobs' ];

let opening = null;
export let durable = true;

function promisify( request ) {
	return new Promise( ( resolve, reject ) => {
		request.onsuccess = () => resolve( request.result );
		request.onerror = () => reject( request.error );
	} );
}

function memoryDb() {
	durable = false;
	const stores = Object.fromEntries( STORES.map( ( name ) => [ name, new Map() ] ) );
	return {
		memory: true,
		async get( store, key ) {
			return stores[ store ].get( key );
		},
		async put( store, value ) {
			const key = value.id || value.key;
			stores[ store ].set( key, value );
			return key;
		},
		async delete( store, key ) {
			stores[ store ].delete( key );
		},
		async all( store ) {
			return [ ...stores[ store ].values() ];
		},
	};
}

function indexedDb( db ) {
	const run = ( store, mode, fn ) =>
		new Promise( ( resolve, reject ) => {
			const tx = db.transaction( store, mode );
			const result = fn( tx.objectStore( store ) );
			tx.oncomplete = () => resolve( result && 'result' in result ? result.result : undefined );
			tx.onerror = () => reject( tx.error );
			tx.onabort = () => reject( tx.error || new Error( 'Transaction aborted' ) );
		} );
	return {
		memory: false,
		get: ( store, key ) => run( store, 'readonly', ( s ) => s.get( key ) ),
		put: ( store, value ) => run( store, 'readwrite', ( s ) => s.put( value ) ),
		delete: ( store, key ) => run( store, 'readwrite', ( s ) => s.delete( key ) ),
		all: ( store ) => run( store, 'readonly', ( s ) => s.getAll() ),
	};
}

function open() {
	if ( ! opening ) {
		opening = ( async () => {
			try {
				if ( ! window.indexedDB ) {
					return memoryDb();
				}
				const request = indexedDB.open( DB_NAME, DB_VERSION );
				request.onupgradeneeded = () => {
					const db = request.result;
					if ( ! db.objectStoreNames.contains( 'outbox' ) ) {
						db.createObjectStore( 'outbox', { keyPath: 'id' } );
					}
					if ( ! db.objectStoreNames.contains( 'blobs' ) ) {
						db.createObjectStore( 'blobs', { keyPath: 'key' } );
					}
				};
				const db = await promisify( request );
				db.onversionchange = () => db.close();
				return indexedDb( db );
			} catch {
				return memoryDb();
			}
		} )();
	}
	return opening;
}

export async function get( store, key ) {
	return ( await open() ).get( store, key );
}

export async function put( store, value ) {
	return ( await open() ).put( store, value );
}

export async function remove( store, key ) {
	return ( await open() ).delete( store, key );
}

export async function all( store ) {
	return ( await open() ).all( store );
}

export async function putBlob( key, blob, name ) {
	return put( 'blobs', { key, blob, name, savedAt: Date.now() } );
}

export async function getBlob( key ) {
	const row = key ? await get( 'blobs', key ) : null;
	return row ? row.blob : null;
}

export async function removeBlob( key ) {
	if ( key ) {
		await remove( 'blobs', key );
	}
}

export function uuid() {
	if ( crypto.randomUUID ) {
		return crypto.randomUUID();
	}
	const bytes = crypto.getRandomValues( new Uint8Array( 16 ) );
	bytes[ 6 ] = ( bytes[ 6 ] & 0x0f ) | 0x40;
	bytes[ 8 ] = ( bytes[ 8 ] & 0x3f ) | 0x80;
	const hex = [ ...bytes ].map( ( b ) => b.toString( 16 ).padStart( 2, '0' ) ).join( '' );
	return `${ hex.slice( 0, 8 ) }-${ hex.slice( 8, 12 ) }-${ hex.slice( 12, 16 ) }-${ hex.slice( 16, 20 ) }-${ hex.slice( 20 ) }`;
}
